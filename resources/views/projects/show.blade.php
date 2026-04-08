@extends('layouts.app')
@section('title', $project->name)
@section('page-title', $project->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
    <li class="breadcrumb-item active">{{ $project->name }}</li>
@endsection

@section('content')
<div class="row">
    {{-- Project info --}}
    <div class="col-md-4">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header"><h3 class="card-title">Project Info</h3>
                <div class="card-tools">
                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Client</dt>
                    <dd class="col-7">{{ $project->client->name }}</dd>
                    <dt class="col-5">Domain</dt>
                    <dd class="col-7"><a href="https://{{ $project->domain_clean }}" target="_blank">{{ $project->domain_clean }}</a></dd>
                    <dt class="col-5">Type</dt>
                    <dd class="col-7"><span class="badge badge-{{ $project->project_type_badge }}">{{ $project->project_type }}</span></dd>
                    <dt class="col-5">Country</dt>
                    <dd class="col-7">{{ $project->country_code }}</dd>
                    <dt class="col-5">Device</dt>
                    <dd class="col-7">{{ $project->device_type }}</dd>
                    <dt class="col-5">Status</dt>
                    <dd class="col-7"><span class="badge badge-{{ $project->status === 'active' ? 'success' : 'secondary' }}">{{ $project->status }}</span></dd>
                    <dt class="col-5">Snapshots</dt>
                    <dd class="col-7"><span class="badge badge-info">{{ $project->snapshots_count }}</span></dd>
                    <dt class="col-5">Keywords</dt>
                    <dd class="col-7"><span class="badge badge-secondary">{{ $project->keywords_count }}</span></dd>
                </dl>
                @if($project->notes)
                    <hr><small class="text-muted">{{ $project->notes }}</small>
                @endif
            </div>
            <div class="card-footer">
                <a href="{{ route('imports.create', ['project_id' => $project->id]) }}" class="btn btn-success btn-sm btn-block">
                    <i class="fas fa-upload mr-1"></i>Import CSV mới
                </a>
            </div>
        </div>

        {{-- Competitors --}}
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header"><h3 class="card-title">Competitors ({{ $competitors->count() }})</h3></div>
            <div class="card-body">
                @if($competitors->isEmpty())
                    <p class="text-muted">Chưa có đối thủ được gán.</p>
                @endif
                @foreach($competitors as $comp)
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge badge-light border mr-2">{{ $comp->project_type }}</span>
                        <a href="{{ route('projects.show', $comp) }}">{{ $comp->domain_clean }}</a>
                        <small class="text-muted ml-1">({{ $comp->name }})</small>
                    </div>
                @endforeach
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-sm btn-outline-warning btn-block" data-toggle="modal" data-target="#competitorModal">
                    <i class="fas fa-users-cog mr-1"></i>Quản lý Competitors
                </button>
            </div>
        </div>
    </div>

    {{-- Snapshots timeline --}}
    <div class="col-md-8">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Snapshot History</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Ngày</th>
                            <th>Tên Snapshot</th>
                            <th class="text-center">Keywords</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($snapshots as $snap)
                        <tr>
                            <td><strong>{{ $snap->report_date->format('d/m/Y') }}</strong></td>
                            <td>{{ $snap->snapshot_name }}</td>
                            <td class="text-center">{{ number_format($snap->total_keywords) }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $snap->status_badge }}">{{ $snap->status }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('dashboard', ['project_id' => $project->id, 'snapshot_id' => $snap->id]) }}"
                                   class="btn btn-xs btn-info">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                                <a href="{{ route('imports.show', $snap) }}" class="btn btn-xs btn-secondary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form method="POST" action="{{ route('imports.destroy', $snap) }}" class="d-inline"
                                      onsubmit="return confirm('Xóa snapshot này?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Chưa có snapshot nào.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($snapshots->count() === 10)
            <div class="card-footer">
                <a href="{{ route('imports.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-secondary">
                    Xem tất cả snapshots
                </a>
            </div>
            @endif
        </div>

        <div class="text-center">
            <a href="{{ route('dashboard', ['project_id' => $project->id]) }}" class="btn btn-primary">
                <i class="fas fa-tachometer-alt mr-1"></i>Xem Dashboard
            </a>
            <a href="{{ route('keywords.index', ['project_id' => $project->id]) }}" class="btn btn-info ml-2">
                <i class="fas fa-search mr-1"></i>Xem Keywords
            </a>
            <a href="{{ route('competitors.index', ['project_id' => $project->id, 'client_id' => $project->client_id]) }}" class="btn btn-warning ml-2">
                <i class="fas fa-users mr-1"></i>Competitor Analysis
            </a>
        </div>
    </div>
</div>

{{-- Competitor management modal --}}
<div class="modal fade" id="competitorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quản lý Competitors — {{ $project->name }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('projects.sync-competitors', $project) }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Chọn các projects là đối thủ cạnh tranh:</p>
                    @php
                        $allProjects = \App\Models\Project::where('id', '!=', $project->id)
                            ->where('status', 'active')
                            ->with('client')
                            ->orderBy('client_id')
                            ->orderBy('name')
                            ->get();
                        $existingIds = $competitors->pluck('id')->toArray();
                    @endphp
                    @foreach($allProjects as $p)
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="cc_{{ $p->id }}"
                               name="competitor_ids[]" value="{{ $p->id }}"
                               {{ in_array($p->id, $existingIds) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="cc_{{ $p->id }}">
                            <strong>{{ $p->client->name }}</strong> — {{ $p->name }} ({{ $p->domain_clean }})
                        </label>
                    </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning">Lưu Competitors</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
