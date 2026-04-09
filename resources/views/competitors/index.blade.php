@extends('layouts.app')
@section('title', 'Competitors')
@section('page-title', 'Competitor Intelligence')
@section('breadcrumb')
    <li class="breadcrumb-item active">Competitors</li>
@endsection

@push('styles')
<style>
th.sortable { cursor:pointer; user-select:none; white-space:nowrap; }
th.sortable:hover { background:rgba(0,0,0,.04); }
th.sortable .sort-icon {
    display:inline-block; width:1em; text-align:center;
    font-style:normal; font-size:.75em; margin-left:2px;
    color:#adb5bd;
}
th.sortable .sort-icon::after        { content:'⇅'; }
th.sortable.asc  .sort-icon::after   { content:'▲'; color:#007bff; }
th.sortable.desc .sort-icon::after   { content:'▼'; color:#007bff; }
</style>
@endpush

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
        <table class="table table-hover mb-0 tbl-sort">
            <thead class="thead-light">
                <tr>
                    <th class="sortable" data-type="text">Domain <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="num">Total KWs <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="num">Visibility Score <span class="sort-icon"></span></th>
                    <th class="text-center">Share of Voice</th>
                    <th class="sortable text-center" data-type="num">Overlap <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="num">Win vs Main <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="text">Snapshot <span class="sort-icon"></span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($analysis['domain_metrics'] as $m)
                <tr class="{{ $m['is_main'] ? 'table-primary font-weight-bold' : '' }}">
                    <td data-val="{{ $m['domain'] }}">
                        @if($m['is_main'])<i class="fas fa-home mr-1 text-primary"></i>@endif
                        {{ $m['domain'] }}
                        @if($m['is_main'])<span class="badge badge-primary ml-1">Main</span>@endif
                    </td>
                    <td class="text-center" data-val="{{ $m['total_keywords'] }}">{{ number_format($m['total_keywords']) }}</td>
                    <td class="text-center" data-val="{{ $m['visibility_score'] }}">{{ number_format($m['visibility_score']) }}</td>
                    <td class="text-center">
                        <div class="progress" style="height:18px;">
                            <div class="progress-bar bg-{{ $m['is_main'] ? 'primary' : 'warning' }}"
                                 style="width:{{ min($m['share_of_voice'], 100) }}%">
                                {{ $m['share_of_voice'] }}%
                            </div>
                        </div>
                    </td>
                    <td class="text-center" data-val="{{ $m['overlap_with_main'] }}">{{ number_format($m['overlap_with_main']) }}</td>
                    <td class="text-center" data-val="{{ $m['is_main'] ? -1 : $m['main_wins'] }}">
                        @if(!$m['is_main'])
                            <span class="text-success font-weight-bold">+{{ $m['main_wins'] }}</span>
                            <span class="text-muted">/</span>
                            <span class="text-danger font-weight-bold">-{{ $m['wins_vs_main'] }}</span>
                        @else —@endif
                    </td>
                    <td class="text-center text-muted small" data-val="{{ $m['snapshot_date'] }}">{{ $m['snapshot_date'] }}</td>
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
            <span class="badge badge-secondary ml-1">{{ $detail['total_count'] }} keywords overlap</span>
            @if($detail['total_count'] > 50)
            <button type="button" class="btn btn-xs btn-outline-secondary ml-2"
                    data-toggle="modal" data-target="#overlapModal{{ $projectId }}">
                <i class="fas fa-expand-alt mr-1"></i>Xem tất cả {{ $detail['total_count'] }} keywords
            </button>
            @endif
        </h5>
        <div style="max-height:250px; overflow-y:auto; margin-bottom:20px;">
        <table class="table table-sm table-bordered mb-0 tbl-sort" style="font-size:.82rem;">
            <thead class="thead-light">
                <tr>
                    <th class="sortable" data-type="text">Keyword <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="num">Main <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="num">{{ $detail['domain'] }} <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="text">Winner <span class="sort-icon"></span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($detail['keywords'] as $kw)
                <tr class="{{ $kw['winner'] === 'competitor' ? 'table-danger' : 'table-success' }}">
                    <td data-val="{{ $kw['keyword'] }}">{{ $kw['keyword'] }}</td>
                    <td class="text-center" data-val="{{ $kw['main_pos'] ?? 9999 }}">{{ $kw['main_pos'] ?? '—' }}</td>
                    <td class="text-center" data-val="{{ $kw['comp_pos'] ?? 9999 }}">{{ $kw['comp_pos'] ?? '—' }}</td>
                    <td class="text-center" data-val="{{ $kw['winner'] }}">
                        <span class="badge badge-{{ $kw['winner'] === 'main' ? 'success' : 'danger' }}">
                            {{ $kw['winner'] === 'main' ? 'Main ✓' : $detail['domain'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        {{-- Modal: tất cả keywords overlap --}}
        @if($detail['total_count'] > 50)
        <div class="modal fade" id="overlapModal{{ $projectId }}" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Tất cả keyword overlap — Main vs <strong>{{ $detail['domain'] }}</strong>
                            <span class="badge badge-secondary ml-1">{{ $detail['total_count'] }} keywords</span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-0" style="max-height:70vh; overflow-y:auto;">
                        <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
                            <thead class="thead-light" style="position:sticky;top:0;z-index:1;">
                                <tr>
                                    <th>#</th>
                                    <th>Keyword</th>
                                    <th class="text-center" style="width:80px;">Main</th>
                                    <th class="text-center" style="width:80px;">{{ $detail['domain'] }}</th>
                                    <th class="text-center" style="width:100px;">Winner</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detail['all_keywords'] as $i => $kw)
                                <tr class="{{ $kw['winner'] === 'competitor' ? 'table-danger' : 'table-success' }}">
                                    <td class="text-muted small">{{ $i + 1 }}</td>
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endif

@endif
@endsection

@push('scripts')
<script>
// ── Table Sort ────────────────────────────────────────────────────────────────
(function () {
    function getVal(td, type) {
        const v = td.dataset.val;
        if (v !== undefined) return type === 'num' ? parseFloat(v) : v.toLowerCase();
        const t = td.textContent.replace(/[▲▼+,\s%]/g, '').trim();
        return type === 'num' ? (parseFloat(t) || 0) : t.toLowerCase();
    }
    document.querySelectorAll('table.tbl-sort').forEach(table => {
        table.querySelectorAll('thead th.sortable').forEach((th, colIdx) => {
            th.addEventListener('click', () => {
                const type = th.dataset.type || 'text';
                const dir  = th.classList.contains('asc') ? 'desc' : 'asc';
                table.querySelectorAll('thead th.sortable').forEach(h => h.classList.remove('asc','desc'));
                th.classList.add(dir);
                const tbody = table.querySelector('tbody');
                [...tbody.querySelectorAll('tr')].sort((a, b) => {
                    const vA = getVal(a.querySelectorAll('td')[colIdx], type);
                    const vB = getVal(b.querySelectorAll('td')[colIdx], type);
                    return (vA < vB ? -1 : vA > vB ? 1 : 0) * (dir === 'asc' ? 1 : -1);
                }).forEach(r => tbody.appendChild(r));
            });
        });
    });
})();
</script>

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
