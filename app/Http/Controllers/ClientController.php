<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::withCount('projects')
            ->withCount(['projects as snapshots_count' => function ($q) {
                $q->join('snapshots', 'snapshots.project_id', '=', 'projects.id');
            }])
            ->latest()
            ->paginate(20);

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'company_name'     => 'nullable|string|max:255',
            'domain'           => 'nullable|string|max:255',
            'contact_name'     => 'nullable|string|max:255',
            'contact_email'    => 'nullable|email|max:255',
            'report_frequency' => 'required|in:weekly,biweekly,monthly,quarterly',
            'notes'            => 'nullable|string|max:2000',
            'logo'             => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')
                ->store('clients/logos', 'public');
        }
        unset($validated['logo']);

        $client = Client::create($validated);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', "Client \"{$client->name}\" đã được tạo.");
    }

    public function show(Client $client)
    {
        $client->loadCount('projects');
        $projects = $client->projects()
            ->withCount('snapshots')
            ->latest()
            ->get();

        return view('clients.show', compact('client', 'projects'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'company_name'     => 'nullable|string|max:255',
            'domain'           => 'nullable|string|max:255',
            'contact_name'     => 'nullable|string|max:255',
            'contact_email'    => 'nullable|email|max:255',
            'report_frequency' => 'required|in:weekly,biweekly,monthly,quarterly',
            'notes'            => 'nullable|string|max:2000',
            'logo'             => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($client->logo_path) {
                Storage::disk('public')->delete($client->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')
                ->store('clients/logos', 'public');
        }
        unset($validated['logo']);

        $client->update($validated);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', "Client đã được cập nhật.");
    }

    public function destroy(Client $client)
    {
        $clientName = $client->name;
        $client->delete(); // SoftDelete

        return redirect()
            ->route('clients.index')
            ->with('success', "Client \"{$clientName}\" đã được xóa.");
    }
}
