<?php

namespace App\Services;

use App\Models\Keyword;
use App\Models\Project;
use App\Models\Snapshot;
use Illuminate\Support\Facades\DB;

/**
 * TimelineService
 *
 * Builds Chart.js-ready datasets from multiple snapshots over time.
 */
class TimelineService
{
    /**
     * Build timeline chart data for a project.
     *
     * @param  Project  $project
     * @param  string   $dateFrom   Y-m-d format
     * @param  string   $dateTo     Y-m-d format
     * @return array    Chart.js compatible structure
     */
    public function buildProjectTimeline(Project $project, string $dateFrom, string $dateTo): array
    {
        $snapshots = Snapshot::where('project_id', $project->id)
            ->where('status', 'completed')
            ->whereBetween('report_date', [$dateFrom, $dateTo])
            ->orderBy('report_date')
            ->get();

        if ($snapshots->isEmpty()) {
            return $this->emptyChartData();
        }

        $labels        = [];
        $avgPositions  = [];
        $top3Counts    = [];
        $top10Counts   = [];
        $top20Counts   = [];
        $top100Counts  = [];
        $totalCounts   = [];
        $visScores     = [];

        foreach ($snapshots as $snap) {
            $labels[] = $snap->report_date->format('d/m/Y');

            $stats = DB::table('keyword_rankings')
                ->where('snapshot_id', $snap->id)
                ->selectRaw('
                    COUNT(*) as total,
                    AVG(CASE WHEN current_position IS NOT NULL THEN current_position END) as avg_pos,
                    SUM(CASE WHEN current_position <= 3   THEN 1 ELSE 0 END) as top3,
                    SUM(CASE WHEN current_position <= 10  THEN 1 ELSE 0 END) as top10,
                    SUM(CASE WHEN current_position <= 20  THEN 1 ELSE 0 END) as top20,
                    SUM(CASE WHEN current_position <= 100 THEN 1 ELSE 0 END) as top100,
                    SUM(visibility_points) as vis_score
                ')
                ->first();

            $avgPositions[] = $stats->avg_pos ? round((float)$stats->avg_pos, 1) : null;
            $top3Counts[]   = (int) $stats->top3;
            $top10Counts[]  = (int) $stats->top10;
            $top20Counts[]  = (int) $stats->top20;
            $top100Counts[] = (int) $stats->top100;
            $totalCounts[]  = (int) $stats->total;
            $visScores[]    = (int) $stats->vis_score;
        }

        return [
            'labels'       => $labels,
            'avgPosition'  => [
                'labels'   => $labels,
                'datasets' => [[
                    'label'           => 'Average Position',
                    'data'            => $avgPositions,
                    'borderColor'     => '#3c8dbc',
                    'backgroundColor' => 'rgba(60, 141, 188, 0.1)',
                    'tension'         => 0.3,
                    'fill'            => true,
                ]],
            ],
            'topGroups' => [
                'labels'   => $labels,
                'datasets' => [
                    ['label' => 'Top 3',   'data' => $top3Counts,  'borderColor' => '#28a745', 'backgroundColor' => 'transparent', 'tension' => 0.3],
                    ['label' => 'Top 10',  'data' => $top10Counts, 'borderColor' => '#17a2b8', 'backgroundColor' => 'transparent', 'tension' => 0.3],
                    ['label' => 'Top 20',  'data' => $top20Counts, 'borderColor' => '#ffc107', 'backgroundColor' => 'transparent', 'tension' => 0.3],
                    ['label' => 'Top 100', 'data' => $top100Counts,'borderColor' => '#fd7e14', 'backgroundColor' => 'transparent', 'tension' => 0.3],
                ],
            ],
            'totalKeywords' => [
                'labels'   => $labels,
                'datasets' => [[
                    'label'           => 'Total Keywords',
                    'data'            => $totalCounts,
                    'borderColor'     => '#605ca8',
                    'backgroundColor' => 'rgba(96, 92, 168, 0.1)',
                    'fill'            => true,
                    'tension'         => 0.3,
                ]],
            ],
            'visibilityScore' => [
                'labels'   => $labels,
                'datasets' => [[
                    'label'       => 'Visibility Score',
                    'data'        => $visScores,
                    'borderColor' => '#00a65a',
                    'backgroundColor' => 'rgba(0, 166, 90, 0.1)',
                    'fill'        => true,
                    'tension'     => 0.3,
                ]],
            ],
        ];
    }

    /**
     * Build position history for a single keyword across all snapshots.
     */
    public function buildKeywordTimeline(Keyword $keyword): array
    {
        $rankings = DB::table('keyword_rankings as kr')
            ->join('snapshots as s', 'kr.snapshot_id', '=', 's.id')
            ->where('kr.keyword_id', $keyword->id)
            ->where('s.status', 'completed')
            ->orderBy('s.report_date')
            ->select(['s.report_date', 'kr.current_position', 'kr.search_volume', 'kr.target_url'])
            ->get();

        $labels    = [];
        $positions = [];

        foreach ($rankings as $r) {
            $labels[]    = \Carbon\Carbon::parse($r->report_date)->format('d/m/Y');
            $positions[] = $r->current_position;
        }

        return [
            'keyword' => $keyword->keyword,
            'chartData' => [
                'labels'   => $labels,
                'datasets' => [[
                    'label'           => $keyword->keyword,
                    'data'            => $positions,
                    'borderColor'     => '#3c8dbc',
                    'backgroundColor' => 'rgba(60, 141, 188, 0.15)',
                    'fill'            => true,
                    'tension'         => 0.3,
                    'pointRadius'     => 5,
                    'pointHoverRadius'=> 7,
                    'spanGaps'        => true,
                ]],
            ],
            'rawData' => $rankings->toArray(),
        ];
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function emptyChartData(): array
    {
        return [
            'labels'          => [],
            'avgPosition'     => ['labels' => [], 'datasets' => []],
            'topGroups'       => ['labels' => [], 'datasets' => []],
            'totalKeywords'   => ['labels' => [], 'datasets' => []],
            'visibilityScore' => ['labels' => [], 'datasets' => []],
        ];
    }
}
