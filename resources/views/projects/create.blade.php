@extends('layouts.app')
@section('title', 'Thêm Project')
@section('page-title', 'Thêm Project mới')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
    <li class="breadcrumb-item active">Thêm mới</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i>Thông tin Project</h3>
            </div>
            <form method="POST" action="{{ route('projects.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Client <span class="text-danger">*</span></label>
                                <select name="client_id" class="form-control @error('client_id') is-invalid @enderror" required>
                                    <option value="">-- Chọn Client --</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}"
                                            {{ (old('client_id') == $client->id || ($selectedClient && $selectedClient->id == $client->id)) ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tên Project <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" placeholder="VD: vnptai.io - Organic" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Domain <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                    </div>
                                    <input type="text" name="domain"
                                           class="form-control @error('domain') is-invalid @enderror"
                                           value="{{ old('domain') }}" placeholder="vnptai.io" required>
                                </div>
                                @error('domain')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Loại Project <span class="text-danger">*</span></label>
                                <select name="project_type" class="form-control @error('project_type') is-invalid @enderror" required>
                                    <option value="main"       {{ old('project_type') === 'main'       ? 'selected' : '' }}>Main — Website chính</option>
                                    <option value="competitor" {{ old('project_type') === 'competitor' ? 'selected' : '' }}>Competitor — Đối thủ</option>
                                    <option value="partner"    {{ old('project_type') === 'partner'    ? 'selected' : '' }}>Partner</option>
                                    <option value="benchmark"  {{ old('project_type') === 'benchmark'  ? 'selected' : '' }}>Benchmark</option>
                                </select>
                                @error('project_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Quốc gia</label>
                                <input type="text" name="country_code" class="form-control"
                                       value="{{ old('country_code', 'VN') }}" placeholder="VN" maxlength="10">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Thiết bị <span class="text-danger">*</span></label>
                                <select name="device_type" class="form-control @error('device_type') is-invalid @enderror" required>
                                    <option value="desktop" {{ old('device_type', 'desktop') === 'desktop' ? 'selected' : '' }}>Desktop</option>
                                    <option value="mobile"  {{ old('device_type') === 'mobile'  ? 'selected' : '' }}>Mobile</option>
                                    <option value="all"     {{ old('device_type') === 'all'     ? 'selected' : '' }}>All</option>
                                </select>
                                @error('device_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Trạng thái <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="active"   selected>Active</option>
                                    <option value="paused">Paused</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-switch pt-2">
                                    <input type="checkbox" name="is_main_project" value="1"
                                           class="custom-control-input" id="isMainSwitch"
                                           {{ old('is_main_project') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="isMainSwitch">
                                        Project chính
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="Mô tả ngắn về project...">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Quay lại
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Tạo Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
