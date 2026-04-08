<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\Snapshot;
use App\Services\KpiCalculatorService;
use App\Services\TimelineService;
use App\Services\CompetitorAnalysisService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private KpiCalculatorService      $kpiService,
        private TimelineService           $timelineService,
        private CompetitorAnalysisService $competitorService
    ) {}

    public function index(Request $request)
    {
        $clients  = Client::orderBy('name')->get(['id', 'name']);
        $projects = collect();
        $snapshots = collect();

        $selectedClient   = null;
        $selectedProject  = null;
        $selectedSnapshot = null;
        $selectedCompetitorIds = [];
        $availableCompetitors  = collect();

        // ── Resolve selections ────────────────────────────────────────────────
        if ($request->filled('client_id')) {
            $selectedClient  = Client::find($request->client_id);
            $projects        = Project::where('client_id', $request->client_id)
                ->where('status', 'active')
                ->orderBy('project_type')
                ->orderBy('name')
                ->get();
        }

        if ($request->filled('project_id')) {
            $selectedProject = Project::find($request->project_id);
            if ($selectedProject) {
                $snapshots = $selectedProject->snapshots()
                    ->where('status', 'completed')
                    ->orderByDesc('report_date')
                    ->get();

                $availableCompetitors = $selectedProject->competitors()
                    ->where('status', 'active')
                    ->get(['projects.id', 'projects.name', 'projects.domain']);
            }
        }

        if ($request->filled('snapshot_id')) {
            $selectedSnapshot = Snapshot::find($request->snapshot_id);
        } elseif ($selectedProject) {
            $selectedSnapshot = $selectedProject->latestSnapshot;
        }

        $selectedCompetitorIds = $request->input('competitors', []);

        // ── Date range for timeline ───────────────────────────────────────────
        $dateRange = $request->input('date_range', '90d');
        [$dateFrom, $dateTo] = $this->resolveDateRange($dateRange);

        // ── Calculate KPIs & Charts ───────────────────────────────────────────
        $kpis            = null;
        $prevKpis        = null;
        $timelineData    = null;
        $competitorData  = null;
        $winners         = [];
        $losers          = [];
        $landingPages    = [];
        $topKeywords     = [];

        $kwSortBy  = $request->input('kw_sort', 'current_position');
        $kwSortDir = $request->input('kw_dir', 'asc');

        if ($selectedSnapshot) {
            $kpis     = $this->kpiService->calculate($selectedSnapshot);
            $prevSnap = $selectedSnapshot->previousSnapshot();
            $prevKpis = $prevSnap ? $this->kpiService->calculate($prevSnap) : null;
            $winners  = $this->kpiService->getTopWinners($selectedSnapshot);
            $losers   = $this->kpiService->getTopLosers($selectedSnapshot);
            $landingPages = $this->kpiService->getTopLandingPages($selectedSnapshot);
            $topKeywords  = $this->kpiService->getTopKeywords($selectedSnapshot, 10, $kwSortBy, $kwSortDir);

            if ($selectedProject) {
                $timelineData = $this->timelineService->buildProjectTimeline(
                    $selectedProject, $dateFrom, $dateTo
                );
            }

            if (!empty($selectedCompetitorIds) && $selectedProject) {
                $competitorData = $this->competitorService->analyze(
                    $selectedProject,
                    $selectedCompetitorIds,
                    $selectedSnapshot->report_date->format('Y-m-d')
                );
            }
        }

        return view('dashboard.index', compact(
            'clients', 'projects', 'snapshots',
            'selectedClient', 'selectedProject', 'selectedSnapshot',
            'availableCompetitors', 'selectedCompetitorIds',
            'dateRange', 'dateFrom', 'dateTo',
            'kpis', 'prevKpis', 'timelineData', 'competitorData',
            'winners', 'losers', 'landingPages', 'topKeywords',
            'kwSortBy', 'kwSortDir'
        ));
    }

    /**
     * API: Return dashboard chart data as JSON
     */
    public function data(Request $request)
    {
        $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'date_range'  => 'nullable|in:7d,30d,90d,6m,12m,all',
        ]);

        $project = Project::findOrFail($request->project_id);
        [$dateFrom, $dateTo] = $this->resolveDateRange($request->input('date_range', '90d'));
        $timelineData = $this->timelineService->buildProjectTimeline($project, $dateFrom, $dateTo);

        return response()->json($timelineData);
    }

    /**
     * API: Trả về toàn bộ landing pages của một snapshot (không giới hạn)
     */
    public function allLandingPages(Request $request)
    {
        $request->validate(['snapshot_id' => 'required|exists:snapshots,id']);

        $pages = \Illuminate\Support\Facades\DB::table('keyword_rankings')
            ->where('snapshot_id', $request->snapshot_id)
            ->whereNotNull('target_url')
            ->groupBy('target_url')
            ->orderByDesc('kw_count')
            ->selectRaw('target_url, COUNT(*) as kw_count, MIN(current_position) as best_pos, ROUND(AVG(current_position),1) as avg_pos, SUM(search_volume) as total_volume')
            ->get();

        return response()->json($pages);
    }

    /**
     * API: Trả về danh sách keywords của một URL trong snapshot
     */
    public function urlKeywords(Request $request)
    {
        $request->validate([
            'snapshot_id' => 'required|exists:snapshots,id',
            'url'         => 'required|string',
        ]);

        $keywords = \Illuminate\Support\Facades\DB::table('keyword_rankings')
            ->join('keywords', 'keywords.id', '=', 'keyword_rankings.keyword_id')
            ->where('keyword_rankings.snapshot_id', $request->snapshot_id)
            ->where('keyword_rankings.target_url', $request->url)
            ->orderBy('keyword_rankings.current_position')
            ->select(
                'keywords.id as keyword_id',
                'keywords.keyword',
                'keyword_rankings.current_position',
                'keyword_rankings.position_change',
                'keyword_rankings.search_volume'
            )
            ->get();

        return response()->json($keywords);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function resolveDateRange(string $range): array
    {
        $dateTo = Carbon::today()->format('Y-m-d');

        $dateFrom = match ($range) {
            '7d'  => Carbon::today()->subDays(7)->format('Y-m-d'),
            '30d' => Carbon::today()->subDays(30)->format('Y-m-d'),
            '90d' => Carbon::today()->subDays(90)->format('Y-m-d'),
            '6m'  => Carbon::today()->subMonths(6)->format('Y-m-d'),
            '12m' => Carbon::today()->subMonths(12)->format('Y-m-d'),
            'all' => '2000-01-01',
            default => Carbon::today()->subDays(90)->format('Y-m-d'),
        };

        return [$dateFrom, $dateTo];
    }
}
