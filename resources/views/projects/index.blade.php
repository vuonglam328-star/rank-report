@extends('layouts.app')
@section('title', 'Projects')
@section('page-title', 'Danh sách Projects')
@section('breadcrumb')
    <li class="breadcrumb-item active">Projects</li>
@endsection

@section('content')
<div class="row mb-3 align-items-center">
    <div class="col">
        <a href="{{ route('projects.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i>Thêm Project mới
        </a>
    </div>
    <div class="col-auto">
        <form method="GET" class="d-flex align-items-center">
            <select name="client_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Tất cả clients</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <select name="project_type" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Tất cả loại</option>
                <option value="main"       {{ request('project_type') === 'main'       ? 'selected' : '' }}>Main</option>
                <option value="competitor" {{ request('project_type') === 'competitor' ? 'selected' : '' }}>Competitor</option>
            </select>
            <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Tất cả status</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="paused"   {{ request('status') === 'paused'   ? 'selected' : '' }}>Paused</option>
                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
        </form>
    </div>
</div>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-globe mr-2"></i>Projects ({{ $projects->total() }})</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Project / Domain</th>
                    <th>Client</th>
                    <th class="text-center">Type</th>
                    <th class="text-center">Snapshots</th>
                    <th class="text-center">Keywords</th>
                    <th class="text-center">Status</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                <tr>
                    <td>
                        <strong>{{ $project->name }}</strong>
                        <br><small class="text-muted">{{ $project->domain_clean }}</small>
                    </td>
                    <td><small>{{ $project->client->name }}</small></td>
                    <td class="text-center">
                        <span class="badge badge-{{ $project->project_type_badge }}">{{ strtoupper($project->project_type) }}</span>
                    </td>
                    <td class="text-center">{{ number_format($project->snapshots_count) }}</td>
                    <td class="text-center">{{ number_format($project->keywords_count) }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $project->status === 'active' ? 'success' : ($project->status === 'paused' ? 'warning' : 'secondary') }}">
                            {{ $project->status }}
                        </span>
                    </td>
                    <td class="text-right">
                        <a href="{{ route('projects.show', $project) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('imports.create', ['project_id' => $project->id]) }}" class="btn btn-xs btn-success" title="Import CSV"><i class="fas fa-upload"></i></a>
                        <a href="{{ route('projects.edit', $project) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('projects.destroy', $project) }}" class="d-inline"
                              onsubmit="return confirm('Xóa project {{ $project->name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-globe fa-2x mb-2 d-block opacity-25"></i>
                        Chưa có project nào. <a href="{{ route('projects.create') }}">Thêm ngay</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($projects->hasPages())
    <div class="card-footer">{{ $projects->links() }}</div>
    @endif
</div>
@endsection
