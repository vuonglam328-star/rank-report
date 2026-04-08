@extends('layouts.app')
@section('title', 'Import History')
@section('page-title', 'Import History')
@section('breadcrumb')
    <li class="breadcrumb-item active">Imports</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col">
        <a href="{{ route('imports.create') }}" class="btn btn-success">
            <i class="fas fa-upload mr-1"></i>Import CSV mới
        </a>
    </div>
    <div class="col-auto">
        <form method="GET" class="d-flex">
            <select name="project_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Tất cả projects</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->client->name }} — {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history mr-2"></i>Lịch sử Import ({{ $snapshots->total() }})</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Ngày</th>
                    <th>Project</th>
                    <th>Client</th>
                    <th class="text-center">Keywords</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Tạo lúc</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($snapshots as $snap)
                <tr>
                    <td><strong>{{ $snap->report_date->format('d/m/Y') }}</strong></td>
                    <td>{{ $snap->project->name }}<br><small class="text-muted">{{ $snap->project->domain_clean }}</small></td>
                    <td>{{ $snap->project->client->name }}</td>
                    <td class="text-center">{{ number_format($snap->total_keywords) }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $snap->status_badge }}">{{ $snap->status }}</span>
                    </td>
                    <td class="text-center text-muted small">{{ $snap->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-right">
                        <a href="{{ route('imports.show', $snap) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('dashboard', ['project_id' => $snap->project_id, 'snapshot_id' => $snap->id]) }}"
                           class="btn btn-xs btn-primary"><i class="fas fa-chart-bar"></i></a>
                        <form method="POST" action="{{ route('imports.destroy', $snap) }}" class="d-inline"
                              onsubmit="return confirm('Xóa snapshot này? Dữ liệu ranking sẽ bị xóa vĩnh viễn.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Chưa có import nào. <a href="{{ route('imports.create') }}">Import CSV đầu tiên</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($snapshots->hasPages())
    <div class="card-footer">{{ $snapshots->links() }}</div>
    @endif
</div>
@endsection
