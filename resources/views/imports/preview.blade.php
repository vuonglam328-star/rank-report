@extends('layouts.app')
@section('title', 'Preview Import')
@section('page-title', 'Bước 3: Preview & Xác nhận')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('imports.index') }}">Imports</a></li>
    <li class="breadcrumb-item active">Preview</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-11">

{{-- Steps --}}
<div class="d-flex justify-content-center align-items-center mb-4">
    <div class="text-center mx-3 text-muted">
        <div class="btn btn-circle btn-success">✓</div>
        <div class="small mt-1">Upload</div>
    </div>
    <div class="flex-grow-1 border-top mx-2"></div>
    <div class="text-center mx-3 text-muted">
        <div class="btn btn-circle btn-success">✓</div>
        <div class="small mt-1">Map Cột</div>
    </div>
    <div class="flex-grow-1 border-top mx-2"></div>
    <div class="text-center mx-3">
        <div class="btn btn-circle btn-primary">3</div>
        <div class="small mt-1 font-weight-bold text-primary">Preview & Xác nhận</div>
    </div>
</div>

{{-- Summary --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-info"><i class="fas fa-globe"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Project</span>
                <span class="info-box-number text-sm">{{ $project->name }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-primary"><i class="fas fa-calendar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Ngày báo cáo</span>
                <span class="info-box-number">{{ \Carbon\Carbon::parse($reportDate)->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-success"><i class="fas fa-file-csv"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Số file CSV</span>
                <span class="info-box-number">{{ $fileCount }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-warning"><i class="fas fa-key"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Tổng keywords (gộp)</span>
                <span class="info-box-number">{{ number_format($totalRows) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Preview table --}}
<div class="card card-outline card-success shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-table mr-2"></i>Preview 20 dòng đầu (file 1/{{ $fileCount }})</h3>
        <div class="card-tools">
            <span class="badge badge-success">Tổng gộp: {{ number_format($totalRows) }} keywords</span>
            @if($fileCount > 1)
                <span class="badge badge-info ml-1">{{ $fileCount }} files</span>
            @endif
        </div>
    </div>
    <div class="card-body p-0" style="overflow-x:auto;">
        <table class="table table-sm table-hover table-bordered mb-0" style="font-size:.82rem;">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Keyword</th>
                    <th class="text-center">Position</th>
                    <th class="text-center">Volume</th>
                    <th>URL</th>
                    <th class="text-center">Branded</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
                @foreach($preview['rows'] as $i => $row)
                <tr>
                    <td class="text-muted">{{ $i + 1 }}</td>
                    <td>{{ $row['keyword'] }}</td>
                    <td class="text-center">
                        @if($row['current_position'])
                            <span class="badge badge-{{ $row['current_position'] <= 10 ? 'success' : ($row['current_position'] <= 50 ? 'warning' : 'secondary') }}">
                                {{ $row['current_position'] }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $row['search_volume'] ? number_format($row['search_volume']) : '—' }}</td>
                    <td class="text-truncate" style="max-width:200px;" title="{{ $row['target_url'] }}">
                        {{ $row['target_url'] ?? '—' }}
                    </td>
                    <td class="text-center">
                        @if($row['brand_flag'])
                            <span class="badge badge-warning">Branded</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ $row['location'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Confirm form --}}
@if(isset($importError))
<div class="alert alert-danger">
    <i class="fas fa-times-circle mr-2"></i>
    <strong>Lỗi:</strong> {{ $importError }}
</div>
@endif

@if(isset($duplicateSnapshot))
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle mr-2"></i>
    <strong>Snapshot đã tồn tại:</strong> {{ $duplicateSnapshot['message'] }}
    <a href="{{ route('imports.show', $duplicateSnapshot['existing_id']) }}" class="alert-link ml-2" target="_blank">
        Xem snapshot hiện có ({{ $duplicateSnapshot['existing_date'] }})
    </a>
</div>
@endif

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-paper-plane mr-2"></i>Xác nhận Import</h3>
    </div>
    <form method="POST" action="{{ route('imports.confirm') }}" id="confirmForm">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        <input type="hidden" name="report_date" value="{{ $reportDate }}">
        @foreach($storagePaths as $path)
            <input type="hidden" name="storage_paths[]" value="{{ $path }}">
        @endforeach
        @foreach($columnMap as $key => $val)
            <input type="hidden" name="column_map[{{ $key }}]" value="{{ $val }}">
        @endforeach

        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="force_overwrite" id="forceOverwrite"
                               class="custom-control-input" value="1"
                               {{ isset($duplicateSnapshot) ? '' : 'disabled' }}>
                        <label class="custom-control-label" for="forceOverwrite">
                            <strong>Ghi đè snapshot cũ</strong>
                            <small class="text-muted d-block">Chỉ cần nếu đã có snapshot cùng ngày</small>
                        </label>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <p class="mb-1">Sẽ import <strong class="text-primary">{{ number_format($totalRows) }} keywords</strong>
                        @if($fileCount > 1)
                            <span class="text-muted small">(gộp từ {{ $fileCount }} files)</span>
                        @endif
                    </p>
                    <small class="text-muted">cho {{ $project->name }} ({{ $project->domain_clean }}) ngày {{ \Carbon\Carbon::parse($reportDate)->format('d/m/Y') }}</small>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('imports.create') }}" class="btn btn-secondary">
                <i class="fas fa-times mr-1"></i>Hủy
            </a>
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary mr-2">
                    <i class="fas fa-arrow-left mr-1"></i>Sửa mapping
                </a>
                <button type="submit" class="btn btn-success" id="importBtn">
                    <i class="fas fa-cloud-upload-alt mr-1"></i>
                    Xác nhận Import ({{ number_format($totalRows) }} keywords)
                </button>
            </div>
        </div>
    </form>
</div>

</div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('confirmForm').addEventListener('submit', function() {
    document.getElementById('importBtn').innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Đang import...';
    document.getElementById('importBtn').disabled = true;
});
@if(isset($duplicateSnapshot))
document.getElementById('forceOverwrite').disabled = false;
@endif
</script>
@endpush
