@extends('layouts.app')
@section('title', isset($template) ? 'Sửa Template' : 'Tạo Template')
@section('page-title', isset($template) ? 'Sửa Template' : 'Tạo Template mới')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item active">{{ isset($template) ? 'Sửa' : 'Tạo mới' }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-palette mr-2"></i>
                    {{ isset($template) ? 'Chỉnh sửa' : 'Tạo' }} Report Template
                </h3>
            </div>

            @php
                $action = isset($template) ? route('templates.update', $template) : route('templates.store');
                $method = isset($template) ? 'PUT' : 'POST';
                $t = $template ?? null;
            @endphp

            <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
                @csrf
                @if($method === 'PUT') @method('PUT') @endif

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tên Template <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $t?->name) }}"
                                       placeholder="VD: Default Agency Template" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tên Agency</label>
                                <input type="text" name="agency_name" class="form-control"
                                       value="{{ old('agency_name', $t?->agency_name) }}"
                                       placeholder="Tên agency của bạn">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Tiêu đề trang bìa</label>
                        <input type="text" name="cover_title" class="form-control"
                               value="{{ old('cover_title', $t?->cover_title ?? 'SEO Performance Report') }}">
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Màu chủ đạo</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text p-1" id="primaryColorPreview"
                                              style="background:{{ old('primary_color', $t?->primary_color ?? '#3c8dbc') }};width:36px;">&nbsp;</span>
                                    </div>
                                    <input type="text" name="primary_color" id="primaryColorInput"
                                           class="form-control"
                                           value="{{ old('primary_color', $t?->primary_color ?? '#3c8dbc') }}"
                                           placeholder="#3c8dbc" maxlength="9">
                                    <div class="input-group-append">
                                        <input type="color" class="form-control form-control-color p-0 border-0"
                                               id="primaryColorPicker"
                                               value="{{ old('primary_color', $t?->primary_color ?? '#3c8dbc') }}"
                                               style="width:40px;cursor:pointer;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Màu phụ</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text p-1" id="secondaryColorPreview"
                                              style="background:{{ old('secondary_color', $t?->secondary_color ?? '#ffffff') }};width:36px;border:1px solid #ced4da;">&nbsp;</span>
                                    </div>
                                    <input type="text" name="secondary_color" id="secondaryColorInput"
                                           class="form-control"
                                           value="{{ old('secondary_color', $t?->secondary_color ?? '#ffffff') }}"
                                           placeholder="#ffffff" maxlength="9">
                                    <div class="input-group-append">
                                        <input type="color" class="form-control form-control-color p-0 border-0"
                                               id="secondaryColorPicker"
                                               value="{{ old('secondary_color', $t?->secondary_color ?? '#ffffff') }}"
                                               style="width:40px;cursor:pointer;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Logo Agency</label>
                                <div class="custom-file">
                                    <input type="file" name="logo" class="custom-file-input" id="logoInput"
                                           accept="image/png,image/jpg,image/jpeg,image/svg+xml">
                                    <label class="custom-file-label" for="logoInput">
                                        {{ $t?->logo_path ? 'Thay logo...' : 'Chọn logo...' }}
                                    </label>
                                </div>
                                @if($t?->logo_path)
                                    <img src="{{ Storage::url($t->logo_path) }}" alt="Current logo"
                                         class="mt-2" style="max-height:40px;">
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="is_default" value="1"
                                           class="custom-control-input" id="isDefaultSwitch"
                                           {{ old('is_default', $t?->is_default) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="isDefaultSwitch">
                                        <strong>Đặt làm template mặc định</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('templates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Quay lại
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>
                        {{ isset($template) ? 'Lưu thay đổi' : 'Tạo Template' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Sync color picker ↔ text input ↔ preview swatch
[['primaryColor','primaryColorInput','primaryColorPicker','primaryColorPreview'],
 ['secondaryColor','secondaryColorInput','secondaryColorPicker','secondaryColorPreview']]
.forEach(([, inputId, pickerId, previewId]) => {
    const input   = document.getElementById(inputId);
    const picker  = document.getElementById(pickerId);
    const preview = document.getElementById(previewId);
    const sync = (val) => { preview.style.background = val; picker.value = val; input.value = val; };
    input.addEventListener('input',  () => sync(input.value));
    picker.addEventListener('input', () => sync(picker.value));
});
</script>
@endpush
