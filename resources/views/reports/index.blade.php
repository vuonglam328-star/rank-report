@extends('layouts.app')
@section('title', 'Báo cáo PDF')
@section('page-title', 'Danh sách Báo cáo')
@section('breadcrumb')
    <li class="breadcrumb-item active">Báo cáo</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col">
        <a href="{{ route('reports.create') }}" class="btn btn-success">
            <i class="fas fa-plus mr-1"></i>Tạo báo cáo mới
        </a>
    </div>
</div>

<div class="card card-outline card-success shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-pdf mr-2"></i>Lịch sử báo cáo ({{ $reports->total() }})</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Tiêu đề</th>
                    <th>Project / Client</th>
                    <th class="text-center">Snapshot</th>
                    <th class="text-center">Template</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-center">Tạo lúc</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                <tr>
                    <td>
                        <strong>{{ $report->report_title }}</strong>
                        @if($report->summary_text)
                            <br><small class="text-muted">{{ Str::limit($report->summary_text, 60) }}</small>
                        @endif
                    </td>
                    <td>
                        {{ $report->project->client->name }}
                        <br><small class="text-muted">{{ $report->project->domain_clean }}</small>
                    </td>
                    <td class="text-center">{{ $report->snapshot->report_date->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <small>{{ $report->template->name }}</small>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-{{ $report->status_badge }}">{{ strtoupper($report->status) }}</span>
                    </td>
                    <td class="text-center text-muted small">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-right">
                        @if($report->status === 'ready')
                        <a href="{{ route('reports.download', $report) }}" class="btn btn-xs btn-success">
                            <i class="fas fa-download mr-1"></i>PDF
                        </a>
                        @endif
                        <form method="POST" action="{{ route('reports.destroy', $report) }}" class="d-inline"
                              onsubmit="return confirm('Xóa báo cáo này?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-file-pdf fa-2x mb-2 d-block opacity-25"></i>
                        Chưa có báo cáo nào. <a href="{{ route('reports.create') }}">Tạo ngay</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reports->hasPages())
    <div class="card-footer">{{ $reports->links() }}</div>
    @endif
</div>
@endsection
