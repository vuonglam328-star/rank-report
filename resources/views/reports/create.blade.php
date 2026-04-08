@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Tạo Báo cáo PDF')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Báo cáo</a></li>
    <li class="breadcrumb-item active">Tạo mới</li>
@endsection

@section('content')
<form method="POST" action="{{ route('reports.store') }}" id="reportForm">
    @csrf
    <input type="hidden" name="chart_images" id="chartImagesInput" value="">

    <div class="row">
        {{-- Left column: Report Config --}}
        <div class="col-lg-4">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-cog mr-2"></i>Cấu hình báo cáo</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Project <span class="text-danger">*</span></label>
                        <select name="project_id" id="projSelect" class="form-control @error('project_id') is-invalid @enderror" required>
                            <option value="">-- Chọn Project --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ (old('project_id') == $p->id || ($selectedProject && $selectedProject->id == $p->id)) ? 'selected' : '' }}>
                                    {{ $p->client->name }} — {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Snapshot <span class="text-danger">*</span></label>
                        <select name="snapshot_id" id="snapshotSelect" class="form-control @error('snapshot_id') is-invalid @enderror" required>
                            <option value="">-- Chọn Snapshot --</option>
                            @foreach($snapshots as $s)
                                <option value="{{ $s->id }}" {{ ($selectedSnapshot && $selectedSnapshot->id == $s->id) ? 'selected' : '' }}>
                                    {{ $s->report_date->format('d/m/Y') }} ({{ number_format($s->total_keywords) }} kw)
                                </option>
                            @endforeach
                        </select>
                        @error('snapshot_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Template <span class="text-danger">*</span></label>
                        <select name="report_template_id" class="form-control @error('report_template_id') is-invalid @enderror" required>
                            @foreach($templates as $t)
                                <option value="{{ $t->id }}" {{ old('report_template_id') == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }} {{ $t->is_default ? '(Default)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('report_template_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Tiêu đề báo cáo <span class="text-danger">*</span></label>
                        <input type="text" name="report_title" class="form-control @error('report_title') is-invalid @enderror"
                               value="{{ old('report_title', 'SEO Performance Report ' . date('m/Y')) }}" required>
                        @error('report_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Executive Summary</label>
                        <textarea name="summary_text" class="form-control" rows="5"
                                  placeholder="Tóm tắt kết quả SEO tháng này...">{{ old('summary_text') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Sections to include --}}
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-list-check mr-2"></i>Sections trong báo cáo</h3></div>
                <div class="card-body">
                    @foreach($availableSections as $key => $label)
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" name="selected_sections[]" value="{{ $key }}"
                               class="custom-control-input" id="sec_{{ $key }}"
                               {{ in_array($key, old('selected_sections', array_keys($availableSections))) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="sec_{{ $key }}">{{ $label }}</label>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Competitors to include --}}
            <div id="competitorSection" style="display:none;">
                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-users mr-2"></i>Đối thủ trong báo cáo</h3></div>
                    <div class="card-body" id="competitorList">
                        {{-- Populated via AJAX --}}
                    </div>
                </div>
            </div>

            {{-- Server-side fallback nếu vào trang với ?project_id --}}
            @if($competitors->isNotEmpty())
            <div id="competitorSectionStatic">
                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-users mr-2"></i>Đối thủ trong báo cáo</h3></div>
                    <div class="card-body">
                        @foreach($competitors as $comp)
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" name="competitor_ids[]" value="{{ $comp->id }}"
                                   class="custom-control-input" id="comp_{{ $comp->id }}"
                                   {{ in_array($comp->id, old('competitor_ids', [])) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="comp_{{ $comp->id }}">
                                {{ $comp->domain_clean }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Right column: Chart preview --}}
        <div class="col-lg-8">
            @if($selectedSnapshot)
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Preview Charts (sẽ embed vào PDF)</h3>
                    <div class="card-tools">
                        <small class="text-muted">{{ $selectedProject->name }} — {{ $selectedSnapshot->report_date->format('d/m/Y') }}</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p class="text-muted small">Xu hướng vị trí trung bình</p>
                            <canvas id="reportAvgPosChart" height="120"></canvas>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small">Phân bổ nhóm vị trí</p>
                            <canvas id="reportDistChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-file-pdf fa-4x mb-3 d-block opacity-25"></i>
                <h5>Chọn project và snapshot để xem preview</h5>
            </div>
            @endif

            {{-- Submit button --}}
            <div class="text-right mt-3">
                <a href="{{ route('reports.index') }}" class="btn btn-secondary mr-2">
                    <i class="fas fa-times mr-1"></i>Hủy
                </a>
                <button type="submit" class="btn btn-success btn-lg" id="generateBtn">
                    <i class="fas fa-file-pdf mr-1"></i>Tạo báo cáo PDF
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
// ── Load snapshots khi chọn project ──────────────────────────────────────────
function loadSnapshots(projectId, selectedId) {
    const sel = document.getElementById('snapshotSelect');
    sel.innerHTML = '<option value="">Đang tải...</option>';
    sel.disabled = true;

    if (!projectId) {
        sel.innerHTML = '<option value="">-- Chọn Snapshot --</option>';
        sel.disabled = false;
        return;
    }

    fetch(`/api/snapshots-by-project/${projectId}`)
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">-- Chọn Snapshot --</option>';
            if (!data.length) {
                sel.innerHTML += '<option disabled>Chưa có snapshot nào</option>';
            } else {
                data.forEach(s => {
                    const date = new Date(s.report_date).toLocaleDateString('vi-VN');
                    const kw   = Number(s.total_keywords).toLocaleString('vi-VN');
                    const opt  = new Option(`${date} (${kw} kw)`, s.id);
                    if (s.id == selectedId) opt.selected = true;
                    sel.add(opt);
                });
                if (!selectedId && sel.options.length > 1) sel.selectedIndex = 1;
            }
            sel.disabled = false;
        })
        .catch(() => {
            sel.innerHTML = '<option value="">-- Lỗi tải snapshot --</option>';
            sel.disabled = false;
        });
}

// ── Load competitors khi chọn project ────────────────────────────────────────
function loadCompetitors(projectId) {
    const section     = document.getElementById('competitorSection');
    const list        = document.getElementById('competitorList');
    const staticSec   = document.getElementById('competitorSectionStatic');

    // Ẩn static section (server-side rendered), dùng dynamic thay thế
    if (staticSec) staticSec.style.display = 'none';

    if (!projectId) {
        section.style.display = 'none';
        list.innerHTML = '';
        return;
    }

    fetch(`/api/competitors-by-project/${projectId}`)
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                section.style.display = 'none';
                list.innerHTML = '';
                return;
            }
            list.innerHTML = data.map(c => `
                <div class="custom-control custom-checkbox mb-2">
                    <input type="checkbox" name="competitor_ids[]" value="${c.id}"
                           class="custom-control-input" id="dyn_comp_${c.id}" checked>
                    <label class="custom-control-label" for="dyn_comp_${c.id}">
                        ${c.domain}
                    </label>
                </div>
            `).join('');
            section.style.display = 'block';
        })
        .catch(() => {
            section.style.display = 'none';
        });
}

// Khi đổi project → load snapshots + competitors mới
document.getElementById('projSelect').addEventListener('change', function () {
    loadSnapshots(this.value, null);
    loadCompetitors(this.value);
});

// Load ngay khi vào trang nếu đã có project được chọn
(function () {
    const projId     = document.getElementById('projSelect').value;
    const snapshotId = '{{ $selectedSnapshot?->id ?? "" }}';
    if (projId) {
        loadSnapshots(projId, snapshotId);
        loadCompetitors(projId);
    }
})();

// ── Capture charts as base64 before submitting ────────────────────────────────
document.getElementById('reportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const images = {};
    const chartMap = {
        reportAvgPosChart: 'avg_position_chart',
        reportDistChart: 'distribution_chart',
    };
    Object.entries(chartMap).forEach(([canvasId, key]) => {
        const chart = Chart.getChart(canvasId);
        if (chart) images[key] = chart.toBase64Image('image/png', 1);
    });
    document.getElementById('chartImagesInput').value = JSON.stringify(images);
    document.getElementById('generateBtn').innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Đang tạo PDF...';
    document.getElementById('generateBtn').disabled = true;
    this.submit();
});

// ── Load chart preview khi có snapshot ───────────────────────────────────────
@if($selectedSnapshot)
(function() {
    const projectId = {{ $selectedProject?->id ?? 'null' }};
    if (!projectId) return;
    fetch(`/api/dashboard-data?project_id=${projectId}&date_range=90d`)
        .then(r => r.json())
        .then(data => {
            if (data.avgPosition?.labels?.length > 0) {
                new Chart(document.getElementById('reportAvgPosChart'), {
                    type: 'line',
                    data: data.avgPosition,
                    options: { responsive: true, scales: { y: { reverse: true, min: 1 } }, animation: { duration: 500 } }
                });
            }
        });
})();
@endif
</script>
@endpush
