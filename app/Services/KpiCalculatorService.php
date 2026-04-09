<?php

namespace App\Services;

use App\Models\Snapshot;
use Illuminate\Support\Facades\DB;

/**
 * KpiCalculatorService
 *
 * Computes all KPI metrics for a given snapshot.
 * Compares against the previous snapshot when available.
 */
class KpiCalculatorService
{
    /**
     * Calculate full KPI set for a snapshot.
     *
     * @return array{
     *   total_keywords: int,
     *   avg_position: float|null,
     *   top_3: int,
     *   top_10: int,
     *   top_20: int,
     *   top_50: int,
     *   top_100: int,
     *   outside_100: int,
     *   improved: int,
     *   declined: int,
     *   unchanged: int,
     *   new_keywords: int,
     *   lost_keywords: int,
     *   visibility_score: int,
     *   position_distribution: array,
     * }
     */
    public function calculate(Snapshot $snapshot): array
    {
        $rankings = DB::table('keyword_rankings')
            ->where('snapshot_id', $snapshot->id)
            ->select([
                'current_position',
                'previous_position',
                'position_change',
                'search_volume',
                'visibility_points',
            ])
            ->get();

        $total          = $rankings->count();
        $positions      = $rankings->whereNotNull('current_position')->pluck('current_position');
        $avgPos         = $positions->isNotEmpty() ? round($positions->avg(), 1) : null;

        $top3    = $positions->filter(fn($p) => $p <= 3)->count();
        $top10   = $positions->filter(fn($p) => $p <= 10)->count();
        $top20   = $positions->filter(fn($p) => $p <= 20)->count();
        $top50   = $positions->filter(fn($p) => $p <= 50)->count();
        $top100  = $positions->filter(fn($p) => $p <= 100)->count();
        $outside = $rankings->where('current_position', null)->count()
                 + $positions->filter(fn($p) => $p > 100)->count();

        $improved  = $rankings->where('position_change', '>', 0)->count();
        $declined  = $rankings->where('position_change', '<', 0)->count();
        $unchanged = $rankings->where('position_change', '=', 0)->count();

        $visibilityScore = (int) $rankings->sum('visibility_points');

        // New & lost keywords (vs previous snapshot)
        [$newKeywords, $lostKeywords] = $this->calculateNewLost($snapshot);

        return [
            'total_keywords'  => $total,
            'avg_position'    => $avgPos,
            'top_3'           => $top3,
            'top_10'          => $top10,
            'top_20'          => $top20,
            'top_50'          => $top50,
            'top_100'         => $top100,
            'outside_100'     => $outside,
            'improved'        => $improved,
            'declined'        => $declined,
            'unchanged'       => $unchanged,
            'new_keywords'    => $newKeywords,
            'lost_keywords'   => $lostKeywords,
            'visibility_score'=> $visibilityScore,
            'position_distribution' => [
                'labels' => ['Top 3', '4-10', '11-20', '21-50', '51-100', 'Outside'],
                'data'   => [$top3, $top10 - $top3, $top20 - $top10, $top50 - $top20, $top100 - $top50, $outside],
                'colors' => ['#28a745', '#20c997', '#17a2b8', '#ffc107', '#fd7e14', '#dc3545'],
            ],
        ];
    }

    /**
     * Get top winners (most improved keywords).
     */
    public function getTopWinners(Snapshot $snapshot, int $limit = 10): array
    {
        return DB::table('keyword_rankings as kr')
            ->join('keywords as k', 'kr.keyword_id', '=', 'k.id')
            ->where('kr.snapshot_id', $snapshot->id)
            ->where('kr.position_change', '>', 0)
            ->whereNotNull('kr.current_position')
            ->orderByDesc('kr.position_change')
            ->limit($limit)
            ->select([
                'k.keyword',
                'kr.current_position',
                'kr.previous_position',
                'kr.position_change',
                'kr.search_volume',
                'kr.target_url',
            ])
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();
    }

    /**
     * Get top losers (most declined keywords).
     */
    public function getTopLosers(Snapshot $snapshot, int $limit = 10): array
    {
        return DB::table('keyword_rankings as kr')
            ->join('keywords as k', 'kr.keyword_id', '=', 'k.id')
            ->where('kr.snapshot_id', $snapshot->id)
            ->where('kr.position_change', '<', 0)
            ->whereNotNull('kr.current_position')
            ->orderBy('kr.position_change')
            ->limit($limit)
            ->select([
                'k.keyword',
                'kr.current_position',
                'kr.previous_position',
                'kr.position_change',
                'kr.search_volume',
                'kr.target_url',
            ])
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();
    }

    /**
     * Calculate KPIs filtered to a specific list of keyword_ids.
     * Used for filtered PDF reports (by URL or selected keywords).
     */
    public function calculateForKeywordIds(Snapshot $snapshot, array $keywordIds): array
    {
        $rankings = DB::table('keyword_rankings')
            ->where('snapshot_id', $snapshot->id)
            ->whereIn('keyword_id', $keywordIds)
            ->select([
                'current_position',
                'previous_position',
                'position_change',
                'search_volume',
                'visibility_points',
            ])
            ->get();

        $total     = $rankings->count();
        $positions = $rankings->whereNotNull('current_position')->pluck('current_position');
        $avgPos    = $positions->isNotEmpty() ? round($positions->avg(), 1) : null;

        $top3    = $positions->filter(fn($p) => $p <= 3)->count();
        $top10   = $positions->filter(fn($p) => $p <= 10)->count();
        $top20   = $positions->filter(fn($p) => $p <= 20)->count();
        $top50   = $positions->filter(fn($p) => $p <= 50)->count();
        $top100  = $positions->filter(fn($p) => $p <= 100)->count();
        $outside = $rankings->whereNull('current_position')->count()
                 + $positions->filter(fn($p) => $p > 100)->count();

        $improved  = $rankings->where('position_change', '>', 0)->count();
        $declined  = $rankings->where('position_change', '<', 0)->count();
        $visScore  = (int) $rankings->sum('visibility_points');

        return [
            'total_keywords'   => $total,
            'avg_position'     => $avgPos,
            'top_3'            => $top3,
            'top_10'           => $top10,
            'top_20'           => $top20,
            'top_50'           => $top50,
            'top_100'          => $top100,
            'outside_100'      => $outside,
            'improved'         => $improved,
            'declined'         => $declined,
            'unchanged'        => $total - $improved - $declined,
            'new_keywords'     => 0,
            'lost_keywords'    => 0,
            'visibility_score' => $visScore,
            'position_distribution' => [
                'labels' => ['Top 3', '4-10', '11-20', '21-50', '51-100', 'Outside'],
                'data'   => [$top3, $top10-$top3, $top20-$top10, $top50-$top20, $top100-$top50, $outside],
                'colors' => ['#28a745','#20c997','#17a2b8','#ffc107','#fd7e14','#dc3545'],
            ],
        ];
    }

    /**
     * Get top winners filtered to keyword_ids.
     */
    public function getTopWinnersForKeywordIds(Snapshot $snapshot, array $keywordIds, int $limit = 15): array
    {
        return DB::table('keyword_rankings as kr')
            ->join('keywords as k', 'kr.keyword_id', '=', 'k.id')
            ->where('kr.snapshot_id', $snapshot->id)
            ->whereIn('kr.keyword_id', $keywordIds)
            ->where('kr.position_change', '>', 0)
            ->whereNotNull('kr.current_position')
            ->orderByDesc('kr.position_change')
            ->limit($limit)
            ->select(['k.keyword','kr.current_position','kr.previous_position','kr.position_change','kr.search_volume','kr.target_url'])
            ->get()->map(fn($r) => (array) $r)->toArray();
    }

    /**
     * Get top losers filtered to keyword_ids.
     */
    public function getTopLosersForKeywordIds(Snapshot $snapshot, array $keywordIds, int $limit = 15): array
    {
        return DB::table('keyword_rankings as kr')
            ->join('keywords as k', 'kr.keyword_id', '=', 'k.id')
            ->where('kr.snapshot_id', $snapshot->id)
            ->whereIn('kr.keyword_id', $keywordIds)
            ->where('kr.position_change', '<', 0)
            ->whereNotNull('kr.current_position')
            ->orderBy('kr.position_change')
            ->limit($limit)
            ->select(['k.keyword','kr.current_position','kr.previous_position','kr.position_change','kr.search_volume','kr.target_url'])
            ->get()->map(fn($r) => (array) $r)->toArray();
    }

    /**
     * Get top keywords by position, with sortable fields.
     */
    public function getTopKeywords(Snapshot $snapshot, int $limit = 10, string $sortBy = 'current_position', string $sortDir = 'asc'): array
    {
        $hasNewCols  = \Schema::hasColumns('keyword_rankings', ['kd', 'organic_traffic']);
        $allowedSort = $hasNewCols
            ? ['current_position', 'search_volume', 'organic_traffic', 'kd']
            : ['current_position', 'search_volume'];
        $allowedDir  = ['asc', 'desc'];
        $sortBy  = in_array($sortBy, $allowedSort) ? $sortBy : 'current_position';
        $sortDir = in_array($sortDir, $allowedDir) ? $sortDir : 'asc';

        $select = [
            'k.keyword',
            'kr.current_position',
            'kr.position_change',
            'kr.search_volume',
            'kr.target_url',
        ];
        if ($hasNewCols) {
            $select[] = 'kr.organic_traffic';
            $select[] = 'kr.kd';
        }

        return DB::table('keyword_rankings as kr')
            ->join('keywords as k', 'kr.keyword_id', '=', 'k.id')
            ->where('kr.snapshot_id', $snapshot->id)
            ->whereNotNull('kr.current_position')
            ->orderBy("kr.{$sortBy}", $sortDir)
            ->limit($limit)
            ->select($select)
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();
    }


    public function getTopLandingPages(Snapshot $snapshot, int $limit = 10): array
    {
        return DB::table('keyword_rankings')
            ->where('snapshot_id', $snapshot->id)
            ->whereNotNull('target_url')
            ->where('current_position', '<=', 10)
            ->groupBy('target_url')
            ->orderByDesc('kw_count')
            ->limit($limit)
            ->selectRaw('target_url, COUNT(*) as kw_count, MIN(current_position) as best_pos, AVG(current_position) as avg_pos, SUM(search_volume) as total_volume')
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function calculateNewLost(Snapshot $snapshot): array
    {
        $prevSnapshot = $snapshot->previousSnapshot();
        if (!$prevSnapshot) {
            return [0, 0];
        }

        // Current keyword IDs
        $currentKwIds = DB::table('keyword_rankings')
            ->where('snapshot_id', $snapshot->id)
            ->whereNotNull('current_position')
            ->pluck('keyword_id')
            ->toArray();

        // Previous keyword IDs
        $prevKwIds = DB::table('keyword_rankings')
            ->where('snapshot_id', $prevSnapshot->id)
            ->whereNotNull('current_position')
            ->pluck('keyword_id')
            ->toArray();

        $currentSet = array_flip($currentKwIds);
        $prevSet    = array_flip($prevKwIds);

        $newKeywords  = count(array_diff_key($currentSet, $prevSet));
        $lostKeywords = count(array_diff_key($prevSet, $currentSet));

        return [$newKeywords, $lostKeywords];
    }
}
