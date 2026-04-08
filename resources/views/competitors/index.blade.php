@extends('layouts.app')
@section('title', 'Competitors')
@section('page-title', 'Competitor Intelligence')
@section('breadcrumb')
    <li class="breadcrumb-item active">Competitors</li>
@endsection

@section('content')
<form method="GET" action="{{ route('competitors.index') }}">
<div class="card card-outline card-warning shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label class="mb-1 small">Client</label>
                <select name="client_id" class="form-control form-control-sm" id="clientSel">
                    <option value="">-- Chọn Client --</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="mb-1 small">Main Project</label>
                <select name="project_id" class="form-control form-control-sm">
                    <option value="">-- Chọn Project --</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->domain_clean }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-warning btn-sm btn-block">
                    <i class="fas fa-filter mr-1"></i>Chọn
                </button>
            </div>
        </div>

        @if($selectedProject && $availableCompetitors->isNotEmpty())
        <div class="row mt-2">
            <div class="col-12">
                <label class="mb-1 small font-weight-bold">Chọn đối thủ để so sánh:</label>
                <div class="d-flex flex-wrap">
                    @foreach($availableCompetitors as $comp)
                    <div class="custom-control custom-checkbox mr-3">
                        <input type="checkbox" class="custom-control-input" id="c_{{ $comp->id }}"
                               name="competitor_ids[]" value="{{ $comp->id }}"
                               {{ in_array($comp->id, $selectedCompetitorIds) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="c_{{ $comp->id }}">
                            {{ $comp->domain_clean }}
                        </label>
                    </div>
                    @endforeach
                    <button type="submit" class="btn btn-sm btn-outline-warning ml-2">Cập nhật</button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
</form>

@if(!$selectedProject)
<div class="text-center py-5 text-muted">
    <i class="fas fa-users fa-4x mb-3 d-block opacity-25"></i>
    <h4>Chọn client và project để xem competitor analysis</h4>
</div>
@elseif(empty($selectedCompetitorIds))
<div class="alert alert-info">
    <i class="fas fa-info-circle mr-2"></i>
    Chọn ít nhất một đối thủ để so sánh.
    @if($availableCompetitors->isEmpty())
        <a href="{{ route('projects.show', $selectedProject) }}" class="alert-link">
            Gán đối thủ cho project này trước.
        </a>
    @endif
</div>
@else

{{-- Domain Metrics Table --}}
<div class="card card-outline card-warning shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-table mr-2"></i>Bảng so sánh — {{ $selectedProject->domain_clean }}</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Domain</th>
                    <th class="text-center">Total KWs</th>
                    <th class="text-center">Visibility Score</th>
                    <th class="text-center">Share of Voice</th>
                    <th class="text-center">Overlap</th>
                    <th class="text-center">Wins vs Main</th>
                    <th class="text-center">Snapshot</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analysis['domain_metrics'] as $m)
                <tr class="{{ $m['is_main'] ? 'table-primary font-weight-bold' : '' }}">
                    <td>
                        @if($m['is_main'])<i class="fas fa-home mr-1 text-primary"></i>@endif
                        {{ $m['domain'] }}
                        @if($m['is_main'])<span class="badge badge-primary ml-1">Main</span>@endif
                    </td>
                    <td class="text-center">{{ number_format($m['total_keywords']) }}</td>
                    <td class="text-center">{{ number_format($m['visibility_score']) }}</td>
                    <td class="text-center">
                        <div class="progress" style="height:18px;">
                            <div class="progress-bar bg-{{ $m['is_main'] ? 'primary' : 'warning' }}"
                                 style="width:{{ min($m['share_of_voice'], 100) }}%">
                                {{ $m['share_of_voice'] }}%
                            </div>
                        </div>
                    </td>
                    <td class="text-center">{{ number_format($m['overlap_with_main']) }}</td>
                    <td class="text-center">
                        @if(!$m['is_main'])
                            <span class="{{ $m['wins_vs_main'] > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ number_format($m['wins_vs_main']) }}
                            </span>
                        @else —@endif
                    </td>
                    <td class="text-center text-muted small">{{ $m['snapshot_date'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- SoV Charts --}}
<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header"><h3 class="card-title">Visibility Score Comparison</h3></div>
            <div class="card-body"><canvas id="visBarChart" height="120"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header"><h3 class="card-title">Share of Voice</h3></div>
            <div class="card-body d-flex justify-content-center">
                <canvas id="sovDoughnut" style="max-height:250px;"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- SoV Trend --}}
@if($sovTimeline && !empty($sovTimeline['labels']))
<div class="card card-outline card-warning shadow-sm">
    <div class="card-header"><h3 class="card-title">Xu hướng Visibility Score theo thời gian</h3></div>
    <div class="card-body"><canvas id="sovTrendChart" height="80"></canvas></div>
</div>
@endif

{{-- Keyword Overlap Details --}}
@if(!empty($analysis['overlap_details']))
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-exchange-alt mr-2"></i>Keyword Overlap Details</h3>
    </div>
    <div class="card-body">
        @foreach($analysis['overlap_details'] as $projectId => $detail)
        <h5>Main vs <strong>{{ $detail['domain'] }}</strong>
            <span class="badge badge-secondary ml-1">{{ count($detail['keywords']) }} keywords overlap</span>
        </h5>
        <div style="max-height:250px; overflow-y:auto; margin-bottom:20px;">
        <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
            <thead class="thead-light">
                <tr>
                    <th>Keyword</th>
                    <th class="text-center">Main</th>
                    <th class="text-center">{{ $detail['domain'] }}</th>
                    <th class="text-center">Winner</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detail['keywords'] as $kw)
                <tr class="{{ $kw['winner'] === 'competitor' ? 'table-danger' : 'table-success' }}">
                    <td>{{ $kw['keyword'] }}</td>
                    <td class="text-center">{{ $kw['main_pos'] ?? '—' }}</td>
                    <td class="text-center">{{ $kw['comp_pos'] ?? '—' }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $kw['winner'] === 'main' ? 'success' : 'danger' }}">
                            {{ $kw['winner'] === 'main' ? 'Main ✓' : $detail['domain'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endforeach
    </div>
</div>
@endif

@endif
@endsection

@push('scripts')
@if($analysis && !empty($analysis['charts']))
<script>
const compCharts = @json($analysis['charts']);

new Chart(document.getElementById('visBarChart'), {
    type: 'bar',
    data: compCharts.visibility_bar,
    options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('sovDoughnut'), {
    type: 'doughnut',
    data: compCharts.sov_doughnut,
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

@if($sovTimeline && !empty($sovTimeline['labels']))
new Chart(document.getElementById('sovTrendChart'), {
    type: 'line',
    data: @json($sovTimeline),
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, title: { display: true, text: 'Visibility Score' } } }
    }
});
@endif
</script>
@endif

<script>
// ── Auto-submit khi đổi Client: load projects → submit ───────────────────────
document.getElementById('clientSel').addEventListener('change', function () {
    const clientId = this.value;
    const projSel  = document.querySelector('select[name="project_id"]');

    projSel.innerHTML = '<option value="">Đang tải...</option>';
    projSel.disabled  = true;

    if (!clientId) {
        projSel.innerHTML = '<option value="">-- Chọn Project --</option>';
        projSel.disabled  = false;
        return;
    }

    fetch(`/api/projects-by-client/${clientId}`)
        .then(r => r.json())
        .then(data => {
            projSel.innerHTML = '<option value="">-- Chọn Project --</option>';
            data.forEach(p => projSel.add(new Option(`${p.name} (${p.domain})`, p.id)));
            projSel.disabled = false;

            // Tự chọn project đầu tiên và submit
            if (data.length > 0) {
                projSel.selectedIndex = 1;
                projSel.closest('form').submit();
            }
        })
        .catch(() => {
            projSel.innerHTML = '<option value="">-- Chọn Project --</option>';
            projSel.disabled  = false;
        });
});

// ── Auto-submit khi đổi Project ───────────────────────────────────────────────
document.querySelector('select[name="project_id"]').addEventListener('change', function () {
    if (this.value) this.closest('form').submit();
});
</script>
@endpush
