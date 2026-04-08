@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Danh sách Clients')
@section('breadcrumb')
    <li class="breadcrumb-item active">Clients</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col">
        <a href="{{ route('clients.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i>Thêm Client mới
        </a>
    </div>
</div>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-building mr-2"></i>Clients ({{ $clients->total() }})</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Client / Company</th>
                    <th>Domain</th>
                    <th>Contact</th>
                    <th class="text-center">Projects</th>
                    <th class="text-center">Tần suất báo cáo</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="{{ $client->logo_url }}" alt="" class="img-circle mr-2" style="width:32px;height:32px;object-fit:cover;">
                            <div>
                                <strong>{{ $client->name }}</strong>
                                @if($client->company_name && $client->company_name !== $client->name)
                                    <br><small class="text-muted">{{ $client->company_name }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($client->domain)
                            <a href="https://{{ $client->domain_clean }}" target="_blank" rel="noopener">
                                {{ $client->domain_clean }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        {{ $client->contact_name ?? '—' }}
                        @if($client->contact_email)
                            <br><small class="text-muted">{{ $client->contact_email }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-info">{{ $client->projects_count }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-secondary">{{ $client->report_frequency }}</span>
                    </td>
                    <td class="text-right">
                        <a href="{{ route('clients.show', $client) }}" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('clients.edit', $client) }}" class="btn btn-xs btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('clients.destroy', $client) }}" class="d-inline"
                              onsubmit="return confirm('Xóa client {{ $client->name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fas fa-building fa-2x mb-2 d-block"></i>
                        Chưa có client nào. <a href="{{ route('clients.create') }}">Thêm ngay</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($clients->hasPages())
    <div class="card-footer">
        {{ $clients->links() }}
    </div>
    @endif
</div>
@endsection
