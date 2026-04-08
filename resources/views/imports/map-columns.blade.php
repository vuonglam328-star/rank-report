@extends('layouts.app')
@section('title', 'Map Cột CSV')
@section('page-title', 'Bước 2: Mapping Cột CSV')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('imports.index') }}">Imports</a></li>
    <li class="breadcrumb-item active">Map Cột</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-10">

{{-- Steps --}}
<div class="d-flex justify-content-center align-items-center mb-4">
    <div class="text-center mx-3 text-muted">
        <div class="btn btn-circle btn-success">✓</div>
        <div class="small mt-1">Upload & Chọn</div>
    </div>
    <div class="flex-grow-1 border-top mx-2"></div>
    <div class="text-center mx-3">
        <div class="btn btn-circle btn-primary">2</div>
        <div class="small mt-1 font-weight-bold text-primary">Map Cột</div>
    </div>
    <div class="flex-grow-1 border-top mx-2"></div>
    <div class="text-center mx-3 text-muted">
        <div class="btn btn-circle btn-secondary">3</div>
        <div class="small mt-1">Preview & Xác nhận</div>
    </div>
</div>

{{-- Duplicate warning --}}
@if($existingSnapshot)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle mr-2"></i>
    <strong>Đã có snapshot</strong> ngày <strong>{{ $existingSnapshot->report_date->format('d/m/Y') }}</strong>
    cho project <strong>{{ $project->name }}</strong> với {{ number_format($existingSnapshot->total_keywords) }} keywords.
    Nếu tiếp tục, bạn sẽ có tùy chọn thay thế hoặc bỏ qua ở bước xác nhận.
</div>
@endif

<div class="card card-outline card-info shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-columns mr-2"></i>Mapping Cột — {{ $project->name }}</h3>
        <div class="card-tools">
            <small class="text-muted">
                @if($fileCount > 1)
                    <span class="badge badge-info mr-2"><i class="fas fa-copy mr-1"></i>{{ $fileCount }} files sẽ được gộp</span>
                @endif
                File: {{ basename($storagePaths[0]) }}
            </small>
        </div>
    </div>
    <form method="POST" action="{{ route('imports.preview') }}">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        <input type="hidden" name="report_date" value="{{ $reportDate }}">
        @foreach($storagePaths as $path)
            <input type="hidden" name="storage_paths[]" value="{{ $path }}">
        @endforeach

        <div class="card-body">
            <p class="text-muted">
                Hệ thống đã tự động nhận diện các cột từ file CSV. Kiểm tra và điều chỉnh nếu cần.
                <span class="badge badge-success ml-1">{{ count($autoMap) }}/{{ count(config('rankreport.csv_ahrefs_columns')) }} cột được nhận diện tự động</span>
            </p>

            <div class="row">
                @php
                    $requiredFields = [
                        'keyword'          => ['label' => 'Keyword 🔑', 'required' => true],
                        'current_position' => ['label' => 'Current Position 🔑', 'required' => true],
                        'volume'           => ['label' => 'Search Volume', 'required' => false],
                        'current_url'      => ['label' => 'Current URL', 'required' => false],
                        'country_code'     => ['label' => 'Country Code', 'required' => false],
                        'location'         => ['label' => 'Location', 'required' => false],
                        'branded'          => ['label' => 'Branded', 'required' => false],
                        'entities'         => ['label' => 'Entities / Type', 'required' => false],
                        'kd'               => ['label' => 'KD (Keyword Difficulty)', 'required' => false],
                        'cpc'              => ['label' => 'CPC', 'required' => false],
                        'organic_traffic'  => ['label' => 'Organic Traffic', 'required' => false],
                        'informational'    => ['label' => 'Informational', 'required' => false],
                        'commercial'       => ['label' => 'Commercial', 'required' => false],
                    ];
                @endphp

                @foreach($requiredFields as $fieldKey => $fieldDef)
                <div class="col-md-6 col-lg-4">
                    <div class="form-group">
                        <label>
                            {{ $fieldDef['label'] }}
                            @if($fieldDef['required'])<span class="text-danger">*</span>@endif
                        </label>
                        <select name="column_map[{{ $fieldKey }}]" class="form-control form-control-sm
                            {{ isset($autoMap[$fieldKey]) ? 'border-success' : '' }}">
                            <option value="">— Bỏ qua —</option>
                            @foreach($csvHeaders as $header)
                                <option value="{{ $header }}"
                                    {{ ($autoMap[$fieldKey] ?? null) === $header ? 'selected' : '' }}>
                                    {{ $header }}
                                </option>
                            @endforeach
                        </select>
                        @if(isset($autoMap[$fieldKey]))
                            <small class="text-success"><i class="fas fa-check mr-1"></i>Auto-detected</small>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- CSV Headers detected --}}
            <div class="mt-3 p-3 bg-light rounded">
                <small class="text-muted font-weight-bold">Cột trong file CSV của bạn:</small>
                <div class="mt-1">
                    @foreach($csvHeaders as $h)
                        <span class="badge badge-light border mr-1 mb-1">{{ $h }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('imports.create') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Quay lại
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-eye mr-1"></i>Xem Preview
            </button>
        </div>
    </form>
</div>

</div>
</div>
@endsection
