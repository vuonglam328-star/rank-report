<?php

namespace App\Services;

use App\Exceptions\DuplicateSnapshotException;
use App\Models\Keyword;
use App\Models\KeywordRanking;
use App\Models\Project;
use App\Models\Snapshot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SnapshotImportService
 *
 * Atomically persists a parsed CSV into:
 *   snapshots → keywords → keyword_rankings
 *
 * Handles:
 * - Duplicate snapshot detection
 * - Previous snapshot lookup for position_change calculation
 * - Batch insert (chunked 500 rows) for performance
 */
class SnapshotImportService
{
    private const BATCH_SIZE = 500;

    public function __construct(
        private VisibilityScoreService $visibilityService
    ) {}

    /**
     * Import parsed CSV rows into the database.
     *
     * @param  Project  $project
     * @param  array    $parsedRows       Output from CsvParserService::parse()['rows']
     * @param  array    $meta             {snapshot_name, report_date, snapshot_type, notes, source_file_path}
     * @param  bool     $forceOverwrite   If true, delete existing snapshot for same date first
     * @return Snapshot
     *
     * @throws DuplicateSnapshotException
     */
    public function import(
        Project $project,
        array   $parsedRows,
        array   $meta,
        bool    $forceOverwrite = false
    ): Snapshot {

        // ── 1. Duplicate check ────────────────────────────────────────────────
        $existing = Snapshot::where('project_id', $project->id)
            ->where('report_date', $meta['report_date'])
            ->first();

        if ($existing && !$forceOverwrite) {
            throw new DuplicateSnapshotException(
                "A snapshot for {$project->domain} on {$meta['report_date']} already exists.",
                $existing
            );
        }

        if ($existing && $forceOverwrite) {
            // Delete existing snapshot and cascade rankings
            $existing->keywordRankings()->delete();
            $existing->delete();
        }

        return DB::transaction(function () use ($project, $parsedRows, $meta) {

            // ── 2. Create snapshot (processing state) ────────────────────────
            $snapshot = Snapshot::create([
                'project_id'       => $project->id,
                'snapshot_name'    => $meta['snapshot_name'] ?? "Snapshot {$meta['report_date']}",
                'report_date'      => $meta['report_date'],
                'snapshot_type'    => $meta['snapshot_type'] ?? 'ahrefs',
                'source_file_path' => $meta['source_file_path'] ?? null,
                'notes'            => $meta['notes'] ?? null,
                'status'           => 'processing',
                'total_keywords'   => 0,
            ]);

            try {

                // ── 3. Load previous snapshot for position_change ─────────────
                $prevRankingsMap = $this->buildPreviousRankingsMap($snapshot);

                // ── 4. Upsert keywords + build ID map ─────────────────────────
                $keywordIdMap = $this->upsertKeywords($project, $parsedRows);

                // ── 5. Batch insert keyword_rankings ─────────────────────────
                $count = $this->insertRankings($snapshot, $parsedRows, $keywordIdMap, $prevRankingsMap);

                // ── 6. Finalize snapshot ──────────────────────────────────────
                $snapshot->update([
                    'status'         => 'completed',
                    'total_keywords' => $count,
                ]);

                Log::info("Snapshot #{$snapshot->id} imported: {$count} keywords for project #{$project->id}");

                return $snapshot;

            } catch (\Throwable $e) {
                $snapshot->update([
                    'status' => 'failed',
                ]);
                Log::error("Snapshot import failed: {$e->getMessage()}", [
                    'project_id'  => $project->id,
                    'report_date' => $meta['report_date'],
                ]);
                throw $e;
            }
        });
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Build a map of normalized_keyword => current_position from previous snapshot.
     *
     * @return array<string, int|null>
     */
    private function buildPreviousRankingsMap(Snapshot $snapshot): array
    {
        $prevSnapshot = $snapshot->previousSnapshot();
        if (!$prevSnapshot) return [];

        return DB::table('keyword_rankings as kr')
            ->join('keywords as k', 'kr.keyword_id', '=', 'k.id')
            ->where('kr.snapshot_id', $prevSnapshot->id)
            ->pluck('kr.current_position', 'k.normalized_keyword')
            ->toArray();
    }

    /**
     * Upsert all keywords for this project.
     * Returns map: normalized_keyword => keyword_id
     *
     * @return array<string, int>
     */
    private function upsertKeywords(Project $project, array $parsedRows): array
    {
        // Collect unique keywords from this import
        $unique = [];
        foreach ($parsedRows as $row) {
            $norm = $row['normalized_keyword'];
            if (!isset($unique[$norm])) {
                $unique[$norm] = $row;
            }
        }

        $keywordIdMap = [];
        $now = now()->toDateTimeString();

        // Batch upsert in chunks
        foreach (array_chunk(array_values($unique), 200, false) as $chunk) {
            $inserts = [];
            foreach ($chunk as $row) {
                $inserts[] = [
                    'project_id'         => $project->id,
                    'keyword'            => $row['keyword'],
                    'normalized_keyword' => $row['normalized_keyword'],
                    'brand_flag'         => $row['brand_flag'] ? 1 : 0,
                    'keyword_type'       => $row['entities'] ?? null,
                    'tag'                => null,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];
            }

            // Insert ignore duplicates (unique constraint)
            DB::table('keywords')->insertOrIgnore($inserts);
        }

        // Now fetch all keyword IDs for this project in one query
        $dbKeywords = DB::table('keywords')
            ->where('project_id', $project->id)
            ->pluck('id', 'normalized_keyword')
            ->toArray();

        return $dbKeywords;
    }

    /**
     * Insert keyword_rankings in batches.
     * Returns number of rows inserted.
     */
    private function insertRankings(
        Snapshot $snapshot,
        array    $parsedRows,
        array    $keywordIdMap,
        array    $prevRankingsMap
    ): int {
        $inserted = 0;
        $now      = now()->toDateTimeString();

        foreach (array_chunk($parsedRows, self::BATCH_SIZE) as $chunk) {
            $inserts = [];
            foreach ($chunk as $row) {
                $norm      = $row['normalized_keyword'];
                $keywordId = $keywordIdMap[$norm] ?? null;
                if (!$keywordId) continue;

                $currentPos  = $row['current_position'];
                $prevPos     = $prevRankingsMap[$norm] ?? null;

                // position_change: positive = improved (rank went up, number went down)
                $posChange = 0;
                if ($currentPos !== null && $prevPos !== null) {
                    $posChange = $prevPos - $currentPos; // e.g. prev=10, cur=6 → change=+4
                }

                $inserts[] = [
                    'snapshot_id'       => $snapshot->id,
                    'keyword_id'        => $keywordId,
                    'current_position'  => $currentPos,
                    'previous_position' => $prevPos,
                    'position_change'   => $posChange,
                    'search_volume'     => $row['search_volume'] ?? 0,
                    'target_url'        => isset($row['target_url']) ? substr($row['target_url'], 0, 1000) : null,
                    'location'          => $row['location'] ?? null,
                    'device'            => null,
                    'visibility_points' => $this->visibilityService->calculate($currentPos),
                    'raw_data_json'     => json_encode($row['_raw'] ?? null),
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
            }

            if (!empty($inserts)) {
                DB::table('keyword_rankings')->insertOrIgnore($inserts);
                $inserted += count($inserts);
            }
        }

        return $inserted;
    }
}
