@extends('layouts.app')
@section('title', $client->name)
@section('page-title', $client->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
    <li class="breadcrumb-item active">{{ $client->name }}</li>
@endsection

@section('content')
<div class="row">
    {{-- Client info card --}}
    <div class="col-md-4">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title">Thông tin Client</h3>
                <div class="card-tools">
                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-xs btn-warning">
                        <i class="fas fa-edit mr-1"></i>Sửa
                    </a>
                </div>
            </div>
            <div class="card-body text-center">
                <img src="{{ $client->logo_url }}" alt="{{ $client->name }}"
                     class="img-circle elevation-2 mb-3" style="width:80px;height:80px;object-fit:cover;">
                <h4 class="mb-0">{{ $client->name }}</h4>
                @if($client->company_name && $client->company_name !== $client->name)
                    <p class="text-muted">{{ $client->company_name }}</p>
                @endif
            </div>
            <div class="card-body pt-0">
                <dl class="row mb-0">
                    @if($client->domain)
                    <dt class="col-5">Domain</dt>
                    <dd class="col-7">
                        <a href="https://{{ $client->domain_clean }}" target="_blank" rel="noopener">
                            {{ $client->domain_clean }}
                        </a>
                    </dd>
                    @endif
                    @if($client->contact_name)
                    <dt class="col-5">Liên hệ</dt>
                    <dd class="col-7">{{ $client->contact_name }}</dd>
                    @endif
                    @if($client->contact_email)
                    <dt class="col-5">Email</dt>
                    <dd class="col-7 text-truncate">
                        <a href="mailto:{{ $client->contact_email }}">{{ $client->contact_email }}</a>
                    </dd>
                    @endif
                    <dt class="col-5">Báo cáo</dt>
                    <dd class="col-7"><span class="badge badge-secondary">{{ $client->report_frequency }}</span></dd>
                    <dt class="col-5">Projects</dt>
                    <dd class="col-7"><span class="badge badge-info">{{ $client->projects_count }}</span></dd>
                    <dt class="col-5">Ngày tạo</dt>
                    <dd class="col-7 text-muted small">{{ $client->created_at->format('d/m/Y') }}</dd>
                </dl>
                @if($client->notes)
                    <hr>
                    <small class="text-muted"><i class="fas fa-sticky-note mr-1"></i>{{ $client->notes }}</small>
                @endif
            </div>
            <div class="card-footer">
                <a href="{{ route('projects.create', ['client_id' => $client->id]) }}" class="btn btn-success btn-sm btn-block">
                    <i class="fas fa-plus mr-1"></i>Thêm Project mới
                </a>
            </div>
        </div>
    </div>

    {{-- Projects list --}}
    <div class="col-md-8">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-globe mr-2"></i>Projects ({{ $projects->count() }})</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Project / Domain</th>
                            <th class="text-center">Type</th>
                            <th class="text-center">Snapshots</th>
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
                            <td class="text-center">
                                <span class="badge badge-{{ $project->project_type_badge }}">
                                    {{ strtoupper($project->project_type) }}
                                </span>
                            </td>
                            <td class="text-center">{{ $project->snapshots_count }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $project->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ $project->status }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-xs btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('dashboard', ['project_id' => $project->id]) }}" class="btn btn-xs btn-primary">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                                <a href="{{ route('imports.create', ['project_id' => $project->id]) }}" class="btn btn-xs btn-success">
                                    <i class="fas fa-upload"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Chưa có project nào.
                                <a href="{{ route('projects.create', ['client_id' => $client->id]) }}">Thêm ngay</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="row">
            <div class="col-md-4">
                <a href="{{ route('dashboard', ['client_id' => $client->id]) }}" class="btn btn-primary btn-block">
                    <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('reports.create') }}" class="btn btn-success btn-block">
                    <i class="fas fa-file-pdf mr-1"></i>Tạo báo cáo
                </a>
            </div>
            <div class="col-md-4">
                <form method="POST" action="{{ route('clients.destroy', $client) }}"
                      onsubmit="return confirm('Xóa client {{ addslashes($client->name) }} và toàn bộ dữ liệu?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-block">
                        <i class="fas fa-trash mr-1"></i>Xóa Client
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
