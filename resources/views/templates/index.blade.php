@extends('layouts.app')
@section('title', 'Templates')
@section('page-title', 'Report Templates')
@section('breadcrumb')
    <li class="breadcrumb-item active">Templates</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col">
        <a href="{{ route('templates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i>Tạo Template mới
        </a>
    </div>
</div>

<div class="row">
    @forelse($templates as $template)
    <div class="col-md-4">
        <div class="card card-outline shadow-sm" style="border-top-color: {{ $template->primary_color }} !important; border-top-width: 3px !important;">
            <div class="card-header">
                <h3 class="card-title">
                    {{ $template->name }}
                    @if($template->is_default)
                        <span class="badge badge-primary ml-1">Default</span>
                    @endif
                </h3>
                <div class="card-tools">
                    <a href="{{ route('templates.edit', $template) }}" class="btn btn-xs btn-warning">
                        <i class="fas fa-edit"></i>
                    </a>
                    @if(!$template->is_default)
                    <form method="POST" action="{{ route('templates.destroy', $template) }}" class="d-inline"
                          onsubmit="return confirm('Xóa template {{ $template->name }}?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div style="width:32px;height:32px;border-radius:50%;background:{{ $template->primary_color }};margin-right:8px;"></div>
                    <div style="width:32px;height:32px;border-radius:50%;background:{{ $template->secondary_color }};border:1px solid #dee2e6;"></div>
                    <small class="text-muted ml-2">{{ $template->primary_color }} / {{ $template->secondary_color }}</small>
                </div>
                <dl class="row mb-0 small">
                    <dt class="col-5">Agency</dt>
                    <dd class="col-7 text-muted">{{ $template->agency_name ?? '—' }}</dd>
                    <dt class="col-5">Cover title</dt>
                    <dd class="col-7 text-muted text-truncate">{{ $template->cover_title ?? '—' }}</dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('reports.create', ['template_id' => $template->id]) }}" class="btn btn-sm btn-outline-success btn-block">
                    <i class="fas fa-file-pdf mr-1"></i>Dùng template này
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center py-5 text-muted">
            <i class="fas fa-palette fa-3x mb-3 d-block opacity-25"></i>
            <h5>Chưa có template nào.</h5>
            <a href="{{ route('templates.create') }}" class="btn btn-primary mt-2">Tạo ngay</a>
        </div>
    </div>
    @endforelse
</div>
@endsection
