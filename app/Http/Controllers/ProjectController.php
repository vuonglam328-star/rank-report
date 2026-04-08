<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectCompetitor;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with('client')
            ->withCount('snapshots')
            ->withCount('keywords');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('project_type')) {
            $query->where('project_type', $request->project_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->latest()->paginate(20)->withQueryString();
        $clients  = Client::orderBy('name')->get(['id', 'name']);

        return view('projects.index', compact('projects', 'clients'));
    }

    public function create(Request $request)
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'domain']);
        $selectedClient = $request->filled('client_id')
            ? Client::find($request->client_id)
            : null;

        return view('projects.create', compact('clients', 'selectedClient'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'name'            => 'required|string|max:255',
            'domain'          => 'required|string|max:255',
            'project_type'    => 'required|in:main,competitor,partner,benchmark',
            'country_code'    => 'nullable|string|max:10',
            'device_type'     => 'required|in:desktop,mobile,all',
            'status'          => 'required|in:active,paused,archived',
            'is_main_project' => 'boolean',
            'notes'           => 'nullable|string|max:2000',
        ]);

        $validated['is_main_project'] = $request->boolean('is_main_project');

        $project = Project::create($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', "Project \"{$project->name}\" đã được tạo.");
    }

    public function show(Project $project)
    {
        $project->load(['client', 'latestSnapshot']);
        $project->loadCount(['snapshots', 'keywords']);
        $competitors = $project->competitors()->with('client')->get();

        $snapshots = $project->snapshots()
            ->where('status', 'completed')
            ->orderBy('report_date', 'desc')
            ->take(10)
            ->get();

        return view('projects.show', compact('project', 'competitors', 'snapshots'));
    }

    public function edit(Project $project)
    {
        $clients = Client::orderBy('name')->get(['id', 'name']);
        return view('projects.edit', compact('project', 'clients'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'name'            => 'required|string|max:255',
            'domain'          => 'required|string|max:255',
            'project_type'    => 'required|in:main,competitor,partner,benchmark',
            'country_code'    => 'nullable|string|max:10',
            'device_type'     => 'required|in:desktop,mobile,all',
            'status'          => 'required|in:active,paused,archived',
            'is_main_project' => 'boolean',
            'notes'           => 'nullable|string|max:2000',
        ]);

        $validated['is_main_project'] = $request->boolean('is_main_project');
        $project->update($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', "Project đã được cập nhật.");
    }

    public function destroy(Project $project)
    {
        $name = $project->name;
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', "Project \"{$name}\" đã được xóa.");
    }

    /**
     * Sync competitor projects for a main project.
     * POST /projects/{project}/competitors
     */
    public function syncCompetitors(Request $request, Project $project)
    {
        $request->validate([
            'competitor_ids'   => 'array',
            'competitor_ids.*' => 'exists:projects,id',
        ]);

        $competitorIds = $request->input('competitor_ids', []);

        // Remove self
        $competitorIds = array_filter($competitorIds, fn($id) => $id != $project->id);

        $project->competitors()->sync($competitorIds);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Danh sách đối thủ đã được cập nhật.');
    }

    /**
     * API: Get projects for a given client (JSON for AJAX selects).
     */
    public function byClient(Client $client)
    {
        $projects = $client->projects()
            ->where('status', 'active')
            ->orderBy('project_type')
            ->orderBy('name')
            ->get(['id', 'name', 'domain', 'project_type']);

        return response()->json($projects);
    }
}
