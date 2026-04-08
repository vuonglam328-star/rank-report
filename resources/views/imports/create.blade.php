@extends('layouts.app')
@section('title', 'Import CSV')
@section('page-title', 'Import CSV Ahrefs')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('imports.index') }}">Imports</a></li>
    <li class="breadcrumb-item active">Bước 1: Upload</li>
@endsection

@push('styles')
<style>
.dropzone-box {
    border: 2px dashed #3c8dbc;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: background .2s;
    background: #f8f9fa;
}
.dropzone-box:hover, .dropzone-box.dragover {
    background: #e8f4fd;
    border-color: #007bff;
}
.dropzone-box .fa-upload { font-size: 2.5rem; color: #3c8dbc; margin-bottom: 10px; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
<div class="col-lg-9">

{{-- Steps indicator --}}
<div class="bs-stepper mb-4">
    <div class="d-flex justify-content-center align-items-center">
        <div class="text-center mx-3">
            <div class="btn btn-circle btn-primary">1</div>
            <div class="small mt-1 font-weight-bold text-primary">Upload & Chọn</div>
        </div>
        <div class="flex-grow-1 border-top mx-2"></div>
        <div class="text-center mx-3 text-muted">
            <div class="btn btn-circle btn-secondary">2</div>
            <div class="small mt-1">Map Cột</div>
        </div>
        <div class="flex-grow-1 border-top mx-2"></div>
        <div class="text-center mx-3 text-muted">
            <div class="btn btn-circle btn-secondary">3</div>
            <div class="small mt-1">Preview & Xác nhận</div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-upload mr-2"></i>Upload file CSV Ahrefs</h3>
    </div>
    <form method="POST" action="{{ route('imports.upload') }}" enctype="multipart/form-data" id="uploadForm">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Project <span class="text-danger">*</span></label>
                        <select name="project_id" class="form-control @error('project_id') is-invalid @enderror" required>
                            <option value="">-- Chọn Project --</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}"
                                    {{ (old('project_id') == $project->id || ($selectedProject && $selectedProject->id == $project->id)) ? 'selected' : '' }}>
                                    {{ $project->client->name }} — {{ $project->name }} ({{ $project->domain_clean }})
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Ngày báo cáo <span class="text-danger">*</span></label>
                        <input type="date" name="report_date" class="form-control @error('report_date') is-invalid @enderror"
                               value="{{ old('report_date', date('Y-m-d')) }}" required>
                        @error('report_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Ghi chú</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Tùy chọn...">
                    </div>
                </div>
            </div>

            {{-- Dropzone --}}
            <div class="form-group">
                <label>File CSV <span class="text-danger">*</span>
                    <small class="text-muted ml-2">(Chọn 1 hoặc nhiều file — tối đa mỗi file {{ config('rankreport.max_csv_size_mb') }}MB)</small>
                </label>
                <div class="dropzone-box" id="dropzone" onclick="document.getElementById('csvInput').click()">
                    <i class="fas fa-upload d-block"></i>
                    <p class="mb-1 font-weight-bold">Kéo thả file CSV vào đây hoặc click để chọn</p>
                    <p class="text-muted small mb-0">Có thể chọn nhiều file CSV cùng lúc — hệ thống sẽ tự gộp keywords</p>
                    <div id="fileListDisplay" class="mt-2"></div>
                </div>
                <input type="file" name="csv_files[]" id="csvInput" accept=".csv,.txt"
                       class="d-none @error('csv_files') is-invalid @enderror" multiple required>
                @error('csv_files')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('csv_files.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            {{-- Tips --}}
            <div class="alert alert-info alert-sm">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Cách export từ Ahrefs:</strong>
                Organic Keywords → Export → CSV. Ahrefs giới hạn 30.000 từ khoá/file — hãy export nhiều file và chọn tất cả cùng lúc để gộp thành 1 snapshot.
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('imports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Quay lại
            </a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="fas fa-arrow-right mr-1"></i>Tiếp theo: Map Cột
            </button>
        </div>
    </form>
</div>

</div>
</div>
@endsection

@push('scripts')
<script>
const csvInput = document.getElementById('csvInput');
const dropzone = document.getElementById('dropzone');
const fileListDisplay = document.getElementById('fileListDisplay');

function renderFileList(files) {
    if (!files || files.length === 0) {
        fileListDisplay.innerHTML = '';
        return;
    }
    let html = '<div class="text-left d-inline-block">';
    for (let i = 0; i < files.length; i++) {
        const size = (files[i].size / 1024 / 1024).toFixed(1);
        html += `<div class="text-primary small"><i class="fas fa-file-csv mr-1"></i>${files[i].name} <span class="text-muted">(${size} MB)</span></div>`;
    }
    html += '</div>';
    fileListDisplay.innerHTML = html;
}

csvInput.addEventListener('change', () => {
    renderFileList(csvInput.files);
});

['dragover','dragenter'].forEach(e => dropzone.addEventListener(e, ev => {
    ev.preventDefault(); dropzone.classList.add('dragover');
}));
['dragleave','dragend'].forEach(e => dropzone.addEventListener(e, () => {
    dropzone.classList.remove('dragover');
}));
dropzone.addEventListener('drop', ev => {
    ev.preventDefault();
    dropzone.classList.remove('dragover');
    const files = ev.dataTransfer.files;
    if (files.length > 0) {
        const dt = new DataTransfer();
        for (let i = 0; i < files.length; i++) {
            if (files[i].name.match(/\.(csv|txt)$/i)) dt.items.add(files[i]);
        }
        csvInput.files = dt.files;
        renderFileList(dt.files);
    }
});
</script>
@endpush
