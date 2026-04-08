@extends('layouts.app')
@section('title', 'Chi tiết Snapshot')
@section('page-title', 'Chi tiết Snapshot')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('imports.index') }}">Imports</a></li>
    <li class="breadcrumb-item active">{{ $snapshot->report_date->format('d/m/Y') }}</li>
@endsection

@section('content')
<div class="row">
    {{-- Snapshot meta --}}
    <div class="col-md-4">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Thông tin Snapshot</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Project</dt>
                    <dd class="col-7">
                        <a href="{{ route('projects.show', $snapshot->project) }}">{{ $snapshot->project->name }}</a>
                    </dd>
                    <dt class="col-5">Client</dt>
                    <dd class="col-7">{{ $snapshot->project->client->name }}</dd>
                    <dt class="col-5">Ngày báo cáo</dt>
                    <dd class="col-7"><strong>{{ $snapshot->report_date->format('d/m/Y') }}</strong></dd>
                    <dt class="col-5">Tổng Keywords</dt>
                    <dd class="col-7">
                        <span class="badge badge-primary badge-lg">{{ number_format($snapshot->total_keywords) }}</span>
                    </dd>
                    <dt class="col-5">Trạng thái</dt>
                    <dd class="col-7">
                        <span class="badge badge-{{ $snapshot->status_badge }}">{{ $snapshot->status }}</span>
                    </dd>
                    <dt class="col-5">Tạo lúc</dt>
                    <dd class="col-7 small text-muted">{{ $snapshot->created_at->format('d/m/Y H:i') }}</dd>
                </dl>
                @if($snapshot->notes)
                    <hr><small class="text-muted">{{ $snapshot->notes }}</small>
                @endif
            </div>
            <div class="card-footer">
                <a href="{{ route('dashboard', ['project_id' => $snapshot->project_id, 'snapshot_id' => $snapshot->id]) }}"
                   class="btn btn-primary btn-sm btn-block">
                    <i class="fas fa-chart-bar mr-1"></i>Xem Dashboard cho snapshot này
                </a>
            </div>
        </div>
    </div>

    {{-- Top rankings preview --}}
    <div class="col-md-8">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-trophy mr-2"></i>Top 20 Keywords</h3>
                <div class="card-tools">
                    <a href="{{ route('keywords.index', ['project_id' => $snapshot->project_id, 'snapshot_id' => $snapshot->id]) }}"
                       class="btn btn-xs btn-outline-success">
                        Xem tất cả <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Keyword</th>
                            <th class="text-center" style="width:70px;">Vị trí</th>
                            <th class="text-center" style="width:90px;">Volume</th>
                            <th class="text-truncate" style="max-width:200px;">URL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topRankings as $r)
                        <tr>
                            <td>{{ $r->keyword->keyword ?? '—' }}</td>
                            <td class="text-center">
                                @php
                                    $pos = $r->current_position;
                                    $badge = $pos <= 3 ? 'success' : ($pos <= 10 ? 'info' : ($pos <= 20 ? 'primary' : 'secondary'));
                                @endphp
                                <span class="badge badge-{{ $badge }} px-2">{{ $pos }}</span>
                            </td>
                            <td class="text-center text-muted small">
                                {{ $r->search_volume > 0 ? number_format($r->search_volume) : '—' }}
                            </td>
                            <td class="text-truncate" style="max-width:200px;" title="{{ $r->target_url }}">
                                <small class="text-info">{{ $r->target_url ? preg_replace('/^https?:\/\/[^\/]+/', '', $r->target_url) : '—' }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Không có dữ liệu</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex justify-content-between">
            <a href="{{ route('imports.index', ['project_id' => $snapshot->project_id]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Danh sách imports
            </a>
            <form method="POST" action="{{ route('imports.destroy', $snapshot) }}"
                  onsubmit="return confirm('Xóa snapshot này? Toàn bộ {{ number_format($snapshot->total_keywords) }} keyword rankings sẽ bị xóa vĩnh viễn.')">
                @csrf @method('DELETE')
                <button class="btn btn-danger">
                    <i class="fas fa-trash mr-1"></i>Xóa Snapshot
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
