@extends('layouts.app')
@section('title', 'Thêm Client')
@section('page-title', 'Thêm Client mới')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
    <li class="breadcrumb-item active">Thêm mới</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i>Thông tin Client</h3>
            </div>
            <form method="POST" action="{{ route('clients.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tên Client <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" placeholder="Tên người liên hệ / alias" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tên Công ty</label>
                                <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                                       value="{{ old('company_name') }}" placeholder="Tên công ty / thương hiệu">
                                @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Domain website</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                    </div>
                                    <input type="text" name="domain" class="form-control @error('domain') is-invalid @enderror"
                                           value="{{ old('domain') }}" placeholder="example.com">
                                </div>
                                @error('domain')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tần suất báo cáo <span class="text-danger">*</span></label>
                                <select name="report_frequency" class="form-control @error('report_frequency') is-invalid @enderror" required>
                                    <option value="weekly"    {{ old('report_frequency') === 'weekly'    ? 'selected' : '' }}>Hàng tuần</option>
                                    <option value="biweekly"  {{ old('report_frequency') === 'biweekly'  ? 'selected' : '' }}>2 tuần/lần</option>
                                    <option value="monthly"   {{ old('report_frequency') === 'monthly'   ? 'selected' : '' }} selected>Hàng tháng</option>
                                    <option value="quarterly" {{ old('report_frequency') === 'quarterly' ? 'selected' : '' }}>Hàng quý</option>
                                </select>
                                @error('report_frequency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Người liên hệ</label>
                                <input type="text" name="contact_name" class="form-control"
                                       value="{{ old('contact_name') }}" placeholder="Họ tên">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email liên hệ</label>
                                <input type="email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror"
                                       value="{{ old('contact_email') }}" placeholder="email@example.com">
                                @error('contact_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Logo</label>
                        <div class="custom-file">
                            <input type="file" name="logo" class="custom-file-input @error('logo') is-invalid @enderror"
                                   id="logoInput" accept="image/png,image/jpg,image/jpeg,image/svg+xml">
                            <label class="custom-file-label" for="logoInput">Chọn file ảnh (PNG/JPG/SVG, max 2MB)</label>
                        </div>
                        @error('logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Ghi chú về client...">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Quay lại
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Lưu Client
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Update file input label
document.getElementById('logoInput').addEventListener('change', function() {
    const label = this.nextElementSibling;
    label.textContent = this.files[0]?.name || 'Chọn file ảnh';
});
</script>
@endpush
