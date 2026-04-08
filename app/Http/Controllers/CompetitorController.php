<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Services\CompetitorAnalysisService;
use App\Services\TimelineService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CompetitorController extends Controller
{
    public function __construct(
        private CompetitorAnalysisService $competitorService,
        private TimelineService           $timelineService
    ) {}

    public function index(Request $request)
    {
        $clients  = Client::orderBy('name')->get(['id', 'name']);
        $projects = collect();
        $availableCompetitors = collect();

        $selectedProject  = null;
        $analysis         = null;
        $sovTimeline      = null;
        $selectedCompetitorIds = $request->input('competitor_ids', []);

        if ($request->filled('client_id')) {
            $projects = Project::where('client_id', $request->client_id)
                ->where('project_type', 'main')
                ->where('status', 'active')
                ->get(['id', 'name', 'domain']);
        }

        if ($request->filled('project_id')) {
            $selectedProject = Project::findOrFail($request->project_id);
            $availableCompetitors = $selectedProject->competitors()
                ->where('status', 'active')
                ->get(['projects.id', 'projects.name', 'projects.domain', 'projects.project_type']);
        }

        if ($selectedProject && !empty($selectedCompetitorIds)) {
            $snapshotDate = $request->filled('snapshot_date')
                ? $request->snapshot_date
                : null;

            $analysis = $this->competitorService->analyze(
                $selectedProject,
                $selectedCompetitorIds,
                $snapshotDate
            );

            // SoV trend (last 90 days)
            $dateFrom = Carbon::today()->subDays(90)->format('Y-m-d');
            $dateTo   = Carbon::today()->format('Y-m-d');
            $sovTimeline = $this->competitorService->buildSovTimeline(
                $selectedProject,
                $selectedCompetitorIds,
                $dateFrom,
                $dateTo
            );
        }

        return view('competitors.index', compact(
            'clients', 'projects',
            'selectedProject', 'availableCompetitors', 'selectedCompetitorIds',
            'analysis', 'sovTimeline'
        ));
    }

    /**
     * API: Get competitor data as JSON
     */
    public function data(Request $request)
    {
        $request->validate([
            'project_id'     => 'required|exists:projects,id',
            'competitor_ids' => 'required|array',
        ]);

        $project  = Project::findOrFail($request->project_id);
        $analysis = $this->competitorService->analyze(
            $project,
            $request->competitor_ids
        );

        return response()->json($analysis);
    }

    /**
     * API: Get projects available as competitors for a main project
     */
    public function byProject(Project $project)
    {
        $competitors = $project->competitors()
            ->where('status', 'active')
            ->get(['projects.id', 'projects.name', 'projects.domain']);

        return response()->json($competitors);
    }
}
