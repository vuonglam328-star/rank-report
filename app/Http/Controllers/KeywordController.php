<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use App\Models\Project;
use App\Models\Snapshot;
use App\Services\TimelineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KeywordController extends Controller
{
    public function __construct(private TimelineService $timelineService) {}

    public function index(Request $request)
    {
        $request->validate([
            'project_id'   => 'nullable|exists:projects,id',
            'snapshot_id'  => 'nullable|exists:snapshots,id',
            'position_group' => 'nullable|in:top_3,top_10,top_20,top_50,top_100,outside',
            'brand'        => 'nullable|in:all,branded,non_branded',
            'search'       => 'nullable|string|max:255',
            'sort'         => 'nullable|in:position,change,volume,keyword,best',
            'direction'    => 'nullable|in:asc,desc',
        ]);

        $projects  = Project::with('client')->orderBy('name')->get(['id', 'name', 'domain', 'client_id']);
        $snapshots = collect();
        $keywords  = collect();

        $selectedProject  = null;
        $selectedSnapshot = null;

        if ($request->filled('project_id')) {
            $selectedProject = Project::find($request->project_id);
            $snapshots = $selectedProject->snapshots()
                ->where('status', 'completed')
                ->orderByDesc('report_date')
                ->get(['id', 'snapshot_name', 'report_date']);
        }

        if ($request->filled('snapshot_id')) {
            $selectedSnapshot = Snapshot::find($request->snapshot_id);
        } elseif ($selectedProject) {
            $selectedSnapshot = $selectedProject->latestSnapshot;
        }

        if ($selectedSnapshot) {
            // Sub-query: best position per keyword across ALL snapshots of this project
            $allSnapshotIds = $selectedProject->snapshots()
                ->where('status', 'completed')
                ->pluck('id');

            $bestPositionSub = DB::table('keyword_rankings')
                ->whereIn('snapshot_id', $allSnapshotIds)
                ->whereNotNull('current_position')
                ->groupBy('keyword_id')
                ->selectRaw('keyword_id, MIN(current_position) as best_position');

            $query = DB::table('keyword_rankings as kr')
                ->join('keywords as k', 'kr.keyword_id', '=', 'k.id')
                ->joinSub($bestPositionSub, 'bp', 'bp.keyword_id', '=', 'kr.keyword_id')
                ->where('kr.snapshot_id', $selectedSnapshot->id)
                ->select([
                    'k.id as keyword_id',
                    'k.keyword',
                    'k.brand_flag',
                    'k.tag',
                    'kr.current_position',
                    'kr.previous_position',
                    'kr.position_change',
                    'kr.search_volume',
                    'kr.target_url',
                    'kr.visibility_points',
                    'bp.best_position',
                ]);

            // Filter by position group
            if ($request->filled('position_group')) {
                $group = config("rankreport.position_groups.{$request->position_group}");
                if ($group) {
                    $query->whereBetween('kr.current_position', [$group['min'], $group['max']]);
                }
            }

            // Filter brand
            if ($request->position_group !== 'outside' && $request->filled('brand') && $request->brand !== 'all') {
                $query->where('k.brand_flag', $request->brand === 'branded' ? 1 : 0);
            }

            // Search keyword
            if ($request->filled('search')) {
                $term = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $request->search);
                $query->where('k.keyword', 'like', '%' . $term . '%');
            }

            // Sort
            $sortMap = [
                'position' => 'kr.current_position',
                'change'   => 'kr.position_change',
                'volume'   => 'kr.search_volume',
                'keyword'  => 'k.keyword',
                'best'     => 'bp.best_position',
            ];
            $sortCol = $sortMap[$request->input('sort', 'position')] ?? 'kr.current_position';
            $sortDir = $request->input('direction', 'asc');

            if ($sortCol === 'kr.position_change') {
                $query->orderByDesc('kr.position_change');
            } else {
                $query->orderBy($sortCol, $sortDir);
            }

            $keywords = $query->paginate(50)->withQueryString();
        }

        return view('keywords.index', compact(
            'projects', 'snapshots', 'keywords',
            'selectedProject', 'selectedSnapshot'
        ));
    }

    /**
     * API: Return keyword position history as JSON (for modal chart)
     */
    public function timeline(Request $request, Keyword $keyword)
    {
        $data = $this->timelineService->buildKeywordTimeline($keyword);
        return response()->json($data);
    }
}
