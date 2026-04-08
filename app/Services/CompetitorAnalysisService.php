<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Snapshot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * CompetitorAnalysisService
 *
 * Calculates competitor metrics based on shared keyword sets:
 * - Keyword overlap and win/loss
 * - Visibility score per domain
 * - Estimated share of voice
 * - SoV trend over time
 */
class CompetitorAnalysisService
{
    private array $chartColors;

    public function __construct()
    {
        $this->chartColors = config('rankreport.chart_colors', []);
    }

    /**
     * Run full competitor analysis for a main project vs selected competitors.
     *
     * @param  Project    $mainProject
     * @param  int[]      $competitorProjectIds
     * @param  string     $snapshotDate   Y-m-d, use latest available if null
     * @return array
     */
    public function analyze(
        Project $mainProject,
        array   $competitorProjectIds,
        ?string $snapshotDate = null,
        array   $filterKeywordIds = []   // optional: limit universe to these keyword_ids
    ): array {

        // Collect all project IDs to compare
        $allProjectIds = array_merge([$mainProject->id], $competitorProjectIds);

        // Find the best snapshot for each project near the given date
        $snapshots = $this->resolveSnapshots($allProjectIds, $snapshotDate);

        if ($snapshots->isEmpty()) {
            return $this->emptyAnalysis();
        }

        // Load all rankings per project (normalized_keyword → position)
        $rankingsByProject = $this->loadRankingsPerProject($snapshots, $filterKeywordIds);

        // Build keyword universe (union of all keywords)
        $universe = $this->buildKeywordUniverse($rankingsByProject);

        // Calculate per-domain metrics
        $domainMetrics = $this->calculateDomainMetrics(
            $rankingsByProject,
            $universe,
            $mainProject->id,
            $competitorProjectIds,
            $snapshots
        );

        // Calculate share of voice
        $totalVisibility = array_sum(array_column($domainMetrics, 'visibility_score'));
        foreach ($domainMetrics as &$m) {
            $m['share_of_voice'] = $totalVisibility > 0
                ? round($m['visibility_score'] / $totalVisibility * 100, 1)
                : 0;
        }
        unset($m);

        // Build comparison charts
        $charts = $this->buildComparisonCharts($domainMetrics);

        // Keyword overlap details
        $overlapDetails = $this->buildOverlapDetails($rankingsByProject, $mainProject->id, $snapshots);

        return [
            'domain_metrics'   => $domainMetrics,
            'charts'           => $charts,
            'universe_size'    => count($universe),
            'overlap_details'  => $overlapDetails,
        ];
    }

    /**
     * Build SoV trend data over time for multiple projects.
     */
    public function buildSovTimeline(
        Project $mainProject,
        array   $competitorProjectIds,
        string  $dateFrom,
        string  $dateTo
    ): array {

        $allProjectIds = array_merge([$mainProject->id], $competitorProjectIds);

        // Get all snapshots for all projects in date range
        $allSnapshots = Snapshot::whereIn('project_id', $allProjectIds)
            ->where('status', 'completed')
            ->whereBetween('report_date', [$dateFrom, $dateTo])
            ->orderBy('report_date')
            ->get()
            ->groupBy('project_id');

        // Build date labels (union of all snapshot dates)
        $allDates = collect();
        foreach ($allSnapshots as $projectSnaps) {
            foreach ($projectSnaps as $snap) {
                $allDates->push($snap->report_date->format('Y-m-d'));
            }
        }
        $allDates = $allDates->unique()->sort()->values();

        if ($allDates->isEmpty()) {
            return ['labels' => [], 'datasets' => []];
        }

        $datasets = [];
        $colorIndex = 0;
        $colors = array_merge(
            [$this->chartColors['main'] ?? '#3c8dbc'],
            $this->chartColors['competitor'] ?? []
        );

        foreach ($allProjectIds as $projectId) {
            $project = Project::find($projectId);
            if (!$project) continue;

            $projectSnaps = $allSnapshots[$projectId] ?? collect();
            $sovByDate    = [];

            foreach ($projectSnaps as $snap) {
                $visScore = DB::table('keyword_rankings')
                    ->where('snapshot_id', $snap->id)
                    ->sum('visibility_points');
                $sovByDate[$snap->report_date->format('Y-m-d')] = (int) $visScore;
            }

            // Calculate SoV per date (relative to all projects on that date)
            $data = [];
            foreach ($allDates as $date) {
                $data[] = $sovByDate[$date] ?? null; // null = no snapshot on this date
            }

            $datasets[] = [
                'label'           => $project->domain_clean,
                'data'            => $data,
                'borderColor'     => $colors[$colorIndex % count($colors)],
                'backgroundColor' => 'transparent',
                'tension'         => 0.3,
                'spanGaps'        => true,
            ];
            $colorIndex++;
        }

        return [
            'labels'   => $allDates->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m/Y'))->toArray(),
            'datasets' => $datasets,
        ];
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function resolveSnapshots(array $projectIds, ?string $snapshotDate): Collection
    {
        $query = Snapshot::whereIn('project_id', $projectIds)
            ->where('status', 'completed');

        if ($snapshotDate) {
            $query->where('report_date', '<=', $snapshotDate);
        }

        // Get latest snapshot per project
        return $query->orderByDesc('report_date')
            ->get()
            ->unique('project_id');
    }

    private function loadRankingsPerProject(Collection $snapshots, array $filterKeywordIds = []): array
    {
        $result = [];

        foreach ($snapshots as $snap) {
            $query = DB::table('keyword_rankings as kr')
                ->join('keywords as k', 'kr.keyword_id', '=', 'k.id')
                ->where('kr.snapshot_id', $snap->id)
                ->whereNotNull('kr.current_position')
                ->select(['k.normalized_keyword', 'kr.current_position', 'kr.visibility_points', 'kr.search_volume']);

            // Filter to specific keyword_ids if provided
            if (!empty($filterKeywordIds)) {
                $query->whereIn('kr.keyword_id', $filterKeywordIds);
            }

            $rankings = $query->get()
                ->keyBy('normalized_keyword')
                ->toArray();

            $result[$snap->project_id] = $rankings;
        }

        return $result;
    }

    private function buildKeywordUniverse(array $rankingsByProject): array
    {
        $universe = [];
        foreach ($rankingsByProject as $rankings) {
            foreach ($rankings as $normKw => $_) {
                $universe[$normKw] = true;
            }
        }
        return $universe;
    }

    private function calculateDomainMetrics(
        array $rankingsByProject,
        array $universe,
        int   $mainProjectId,
        array $competitorIds,
        Collection $snapshots
    ): array {
        $metrics = [];

        foreach ($snapshots as $snap) {
            $projectId = $snap->project_id;
            $project   = Project::find($projectId);
            if (!$project) continue;

            $rankings       = $rankingsByProject[$projectId] ?? [];
            $visibilityScore= 0;
            $kwCount        = count($rankings);
            $overlapWithMain= 0;
            $winCount       = 0; // keywords where this project ranks higher than main

            $mainRankings   = $rankingsByProject[$mainProjectId] ?? [];

            foreach ($rankings as $normKw => $r) {
                $visibilityScore += (int) $r->visibility_points;

                if (isset($mainRankings[$normKw])) {
                    $overlapWithMain++;
                    if ($projectId !== $mainProjectId) {
                        $mainPos = $mainRankings[$normKw]->current_position ?? 999;
                        $compPos = $r->current_position ?? 999;
                        if ($compPos < $mainPos) {
                            $winCount++;
                        }
                    }
                }
            }

            $metrics[$projectId] = [
                'project_id'       => $projectId,
                'domain'           => $project->domain_clean,
                'project_type'     => $project->project_type,
                'is_main'          => $projectId === $mainProjectId,
                'total_keywords'   => $kwCount,
                'visibility_score' => $visibilityScore,
                'share_of_voice'   => 0, // filled later
                'overlap_with_main'=> $overlapWithMain,
                'wins_vs_main'     => $projectId !== $mainProjectId ? $winCount : 0,
                'snapshot_date'    => $snap->report_date->format('d/m/Y'),
            ];
        }

        // Sort: main first, then by visibility desc
        uasort($metrics, function ($a, $b) {
            if ($a['is_main']) return -1;
            if ($b['is_main']) return 1;
            return $b['visibility_score'] - $a['visibility_score'];
        });

        return array_values($metrics);
    }

    private function buildComparisonCharts(array $domainMetrics): array
    {
        $labels     = array_column($domainMetrics, 'domain');
        $visibility = array_column($domainMetrics, 'visibility_score');
        $sov        = array_column($domainMetrics, 'share_of_voice');
        $colors     = array_merge(
            [$this->chartColors['main'] ?? '#3c8dbc'],
            $this->chartColors['competitor'] ?? []
        );

        // Assign colors
        $bgColors = [];
        foreach ($labels as $i => $_) {
            $bgColors[] = $colors[$i % count($colors)];
        }

        return [
            'visibility_bar' => [
                'labels'   => $labels,
                'datasets' => [[
                    'label'           => 'Visibility Score',
                    'data'            => $visibility,
                    'backgroundColor' => $bgColors,
                ]],
            ],
            'sov_doughnut' => [
                'labels'   => $labels,
                'datasets' => [[
                    'data'            => $sov,
                    'backgroundColor' => $bgColors,
                ]],
            ],
        ];
    }

    private function buildOverlapDetails(
        array      $rankingsByProject,
        int        $mainProjectId,
        Collection $snapshots
    ): array {
        $mainRankings = $rankingsByProject[$mainProjectId] ?? [];
        $details      = [];

        foreach ($snapshots as $snap) {
            $projectId = $snap->project_id;
            if ($projectId === $mainProjectId) continue;

            $compRankings = $rankingsByProject[$projectId] ?? [];
            $project      = Project::find($projectId);

            $overlap = [];
            foreach ($mainRankings as $normKw => $mainR) {
                if (!isset($compRankings[$normKw])) continue;
                $compR = $compRankings[$normKw];
                $mainPos = $mainR->current_position ?? 999;
                $compPos = $compR->current_position ?? 999;
                $overlap[] = [
                    'keyword'    => $normKw,
                    'main_pos'   => $mainPos < 999 ? $mainPos : null,
                    'comp_pos'   => $compPos < 999 ? $compPos : null,
                    'winner'     => $mainPos <= $compPos ? 'main' : 'competitor',
                ];
            }

            // Sort: tie-wins first (where competitor beats main)
            usort($overlap, fn($a, $b) => ($a['winner'] === 'competitor' ? 0 : 1) - ($b['winner'] === 'competitor' ? 0 : 1));

            $details[$projectId] = [
                'domain'    => $project?->domain_clean ?? 'Unknown',
                'keywords'  => array_slice($overlap, 0, 50), // limit for display
            ];
        }

        return $details;
    }

    private function emptyAnalysis(): array
    {
        return [
            'domain_metrics'  => [],
            'charts'          => [],
            'universe_size'   => 0,
            'overlap_details' => [],
        ];
    }
}
