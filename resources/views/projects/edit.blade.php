@extends('layouts.app')
@section('title', 'Sửa Project')
@section('page-title', 'Sửa Project: ' . $project->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></li>
    <li class="breadcrumb-item active">Sửa</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Chỉnh sửa Project</h3>
            </div>
            <form method="POST" action="{{ route('projects.update', $project) }}">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Client <span class="text-danger">*</span></label>
                                <select name="client_id" class="form-control @error('client_id') is-invalid @enderror" required>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}"
                                            {{ old('client_id', $project->client_id) == $client->id ? 'selected' : '' }}>
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
                                       value="{{ old('name', $project->name) }}" required>
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
                                           value="{{ old('domain', $project->domain) }}" required>
                                </div>
                                @error('domain')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Loại Project <span class="text-danger">*</span></label>
                                <select name="project_type" class="form-control" required>
                                    @foreach(['main'=>'Main — Website chính','competitor'=>'Competitor — Đối thủ','partner'=>'Partner','benchmark'=>'Benchmark'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('project_type', $project->project_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Quốc gia</label>
                                <input type="text" name="country_code" class="form-control"
                                       value="{{ old('country_code', $project->country_code) }}" maxlength="10">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Thiết bị</label>
                                <select name="device_type" class="form-control">
                                    @foreach(['desktop','mobile','all'] as $d)
                                        <option value="{{ $d }}" {{ old('device_type', $project->device_type) === $d ? 'selected' : '' }}>{{ ucfirst($d) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    @foreach(['active','paused','archived'] as $s)
                                        <option value="{{ $s }}" {{ old('status', $project->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-switch pt-2">
                                    <input type="checkbox" name="is_main_project" value="1"
                                           class="custom-control-input" id="isMainSwitch"
                                           {{ old('is_main_project', $project->is_main_project) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="isMainSwitch">Project chính</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $project->notes) }}</textarea>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Quay lại
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
