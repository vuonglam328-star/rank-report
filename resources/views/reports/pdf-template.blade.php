<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<style>
/* ── PDF Base Styles (mPDF compatible — no flex/grid/CSS vars) ── */
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: dejavusans, sans-serif;
    font-size: 10pt;
    color: #2c3e50;
    line-height: 1.5;
}
h1 { font-size: 22pt; color: {{ $template->primary_color ?? '#3c8dbc' }}; }
h2 { font-size: 14pt; color: {{ $template->primary_color ?? '#3c8dbc' }}; border-bottom: 2px solid {{ $template->primary_color ?? '#3c8dbc' }}; padding-bottom: 4pt; margin-bottom: 10pt; }
h3 { font-size: 11pt; color: #34495e; margin-bottom: 6pt; }
p  { margin-bottom: 6pt; }
table { width: 100%; border-collapse: collapse; margin-bottom: 12pt; }
th    { background: {{ $template->primary_color ?? '#3c8dbc' }}; color: #fff; padding: 5pt 8pt; text-align: left; font-size: 9pt; }
td    { padding: 4pt 8pt; border-bottom: 1px solid #ecf0f1; font-size: 9pt; }
tr:nth-child(even) td { background: #f8f9fa; }
.page-break  { page-break-before: always; }
.text-center { text-align: center; }
.text-right  { text-align: right; }
.text-muted  { color: #7f8c8d; }
.badge       { display: inline-block; padding: 2pt 6pt; border-radius: 3pt; font-size: 8pt; font-weight: bold; }
.badge-success { background: #27ae60; color: #fff; }
.badge-danger  { background: #e74c3c; color: #fff; }
.badge-info    { background: #2980b9; color: #fff; }
.badge-warning { background: #f39c12; color: #fff; }
.badge-secondary { background: #95a5a6; color: #fff; }
.kpi-table td { padding: 6pt 10pt; font-size: 10pt; }
.kpi-value { font-size: 18pt; font-weight: bold; color: {{ $template->primary_color ?? '#3c8dbc' }}; }
.change-up   { color: #27ae60; font-weight: bold; }
.change-down { color: #e74c3c; font-weight: bold; }
.section     { margin-bottom: 20pt; }
.cover-header { text-align: center; padding: 60pt 0 30pt; }
.cover-logo   { text-align: center; margin-bottom: 20pt; }
.cover-meta   { text-align: center; color: #7f8c8d; font-size: 11pt; }
</style>
</head>
<body>

{{-- ══════════════════════════════ COVER ══════════════════════════════ --}}
@if(in_array('cover', $sections))
<div class="section cover-header">
    @if($template->logo_path)
    <div class="cover-logo">
        <img src="{{ $template->logo_url }}" alt="" style="max-height: 60pt; max-width: 200pt;">
    </div>
    @endif
    <h1>{{ $report->report_title }}</h1>
    <div class="cover-meta" style="margin-top:20pt;">
        <p><strong>Client:</strong> {{ $project->client->company_name ?? $project->client->name }}</p>
        <p><strong>Domain:</strong> {{ $project->domain_clean }}</p>
        <p><strong>Báo cáo ngày:</strong> {{ $snapshot->report_date->format('d/m/Y') }}</p>
        <p><strong>Tạo bởi:</strong> {{ $template->agency_name ?? 'RankReport Pro' }}</p>
        <p style="margin-top:10pt; color:#aaa; font-size:8pt;">Tạo lúc: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</div>
@endif

{{-- ══════════════════════════ EXECUTIVE SUMMARY ════════════════════════════ --}}
@if(in_array('executive_summary', $sections) && $report->summary_text)
<div class="page-break section">
    <h2>Executive Summary</h2>
    <p>{{ $report->summary_text }}</p>
</div>
@endif

{{-- ══════════════════════════════ KPI SUMMARY ═══════════════════════════════ --}}
@if(in_array('kpi_summary', $sections))
<div class="{{ in_array('executive_summary', $sections) && $report->summary_text ? '' : 'page-break' }} section">
    <h2>KPI Summary — {{ $snapshot->report_date->format('d/m/Y') }}</h2>
    <table class="kpi-table">
        <tr>
            <td><strong>Total Keywords</strong></td>
            <td class="kpi-value">{{ number_format($kpis['total_keywords']) }}</td>
            <td><strong>Average Position</strong></td>
            <td class="kpi-value">{{ $kpis['avg_position'] !== null ? number_format($kpis['avg_position'], 1) : '—' }}</td>
            <td><strong>Visibility Score</strong></td>
            <td class="kpi-value">{{ number_format($kpis['visibility_score']) }}</td>
        </tr>
        <tr>
            <td><strong>Top 3</strong></td>
            <td class="kpi-value" style="color:#27ae60;">{{ number_format($kpis['top_3']) }}</td>
            <td><strong>Top 10</strong></td>
            <td class="kpi-value" style="color:#27ae60;">{{ number_format($kpis['top_10']) }}</td>
            <td><strong>Top 20</strong></td>
            <td class="kpi-value" style="color:#2980b9;">{{ number_format($kpis['top_20']) }}</td>
        </tr>
        <tr>
            <td><strong>Improved</strong></td>
            <td class="kpi-value" style="color:#27ae60;">▲ {{ number_format($kpis['improved']) }}</td>
            <td><strong>Declined</strong></td>
            <td class="kpi-value" style="color:#e74c3c;">▼ {{ number_format($kpis['declined']) }}</td>
            <td><strong>New Keywords</strong></td>
            <td class="kpi-value" style="color:#2980b9;">+{{ number_format($kpis['new_keywords']) }}</td>
        </tr>
        <tr>
            <td><strong>Top 50</strong></td>
            <td class="kpi-value">{{ number_format($kpis['top_50']) }}</td>
            <td><strong>Top 100</strong></td>
            <td class="kpi-value">{{ number_format($kpis['top_100']) }}</td>
            <td><strong>Lost Keywords</strong></td>
            <td class="kpi-value" style="color:#e74c3c;">-{{ number_format($kpis['lost_keywords']) }}</td>
        </tr>
    </table>

    @if($prevKpis)
    <h3>So sánh với kỳ trước</h3>
    <table>
        <thead>
            <tr><th>Chỉ số</th><th class="text-right">Kỳ này</th><th class="text-right">Kỳ trước</th><th class="text-right">Thay đổi</th></tr>
        </thead>
        <tbody>
            @foreach([
                ['Total Keywords', 'total_keywords'],
                ['Avg Position', 'avg_position'],
                ['Top 3', 'top_3'],
                ['Top 10', 'top_10'],
                ['Top 20', 'top_20'],
                ['Visibility Score', 'visibility_score'],
            ] as [$label, $key])
            <tr>
                <td>{{ $label }}</td>
                <td class="text-right">{{ $kpis[$key] !== null ? number_format((float)$kpis[$key], is_float($kpis[$key]) ? 1 : 0) : '—' }}</td>
                <td class="text-right text-muted">{{ $prevKpis[$key] !== null ? number_format((float)$prevKpis[$key], is_float($prevKpis[$key]) ? 1 : 0) : '—' }}</td>
                <td class="text-right">
                    @php
                        $curr = (float)($kpis[$key] ?? 0);
                        $prev = (float)($prevKpis[$key] ?? 0);
                        $diff = $curr - $prev;
                        $isPositiveGood = ($key !== 'avg_position');
                        $isGood = $isPositiveGood ? $diff > 0 : $diff < 0;
                    @endphp
                    @if($diff != 0)
                        <span class="{{ $isGood ? 'change-up' : 'change-down' }}">
                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 1) }}
                        </span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endif

{{-- ════════════════════════════ POSITION CHART ════════════════════════════ --}}
@if(in_array('position_chart', $sections) && !empty($chartImages['avg_position_chart']))
<div class="page-break section">
    <h2>Xu hướng Vị trí Trung bình</h2>
    <div class="text-center">
        <img src="{{ $chartImages['avg_position_chart'] }}" style="max-width:100%; height:auto;" alt="Average Position Chart">
    </div>
</div>
@endif

{{-- ══════════════════════════ DISTRIBUTION CHART ══════════════════════════ --}}
@if(in_array('distribution_chart', $sections) && !empty($chartImages['distribution_chart']))
<div class="page-break section">
    <h2>Phân bổ Nhóm Vị trí</h2>
    <div class="text-center">
        <img src="{{ $chartImages['distribution_chart'] }}" style="max-width:60%; height:auto;" alt="Distribution Chart">
    </div>
    <table style="margin-top:10pt;">
        <thead>
            <tr><th>Nhóm</th><th class="text-right">Số Keywords</th><th class="text-right">%</th></tr>
        </thead>
        <tbody>
            @php $dist = $kpis['position_distribution']; @endphp
            @foreach($dist['labels'] as $i => $label)
            <tr>
                <td>{{ $label }}</td>
                <td class="text-right">{{ number_format($dist['data'][$i]) }}</td>
                <td class="text-right">
                    @php $total = array_sum($dist['data']); @endphp
                    {{ $total > 0 ? round($dist['data'][$i] / $total * 100, 1) : 0 }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ════════════════════════════ TOP KEYWORDS ═════════════════════════════ --}}
@if(in_array('top_keywords', $sections))
<div class="page-break section">
    <h2>Top Keywords — Tăng & Giảm</h2>
    <h3>🏆 Top 15 Keywords Tăng Hạng</h3>
    <table autosize="1">
        <thead>
            <tr><th>Keyword</th><th class="text-right">Vị trí hiện tại</th><th class="text-right">Vị trí trước</th><th class="text-right">Thay đổi</th><th class="text-right">Volume</th></tr>
        </thead>
        <tbody>
            @forelse($winners as $w)
            <tr>
                <td>{{ is_array($w) ? ($w['keyword'] ?? '') : ($w->keyword ?? '') }}</td>
                <td class="text-right"><span class="badge badge-success">{{ is_array($w) ? $w['current_position'] : $w->current_position }}</span></td>
                <td class="text-right text-muted">{{ is_array($w) ? ($w['previous_position'] ?? '—') : ($w->previous_position ?? '—') }}</td>
                <td class="text-right change-up">▲ +{{ is_array($w) ? $w['position_change'] : $w->position_change }}</td>
                <td class="text-right">{{ number_format(is_array($w) ? ($w['search_volume'] ?? 0) : ($w->search_volume ?? 0)) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">Không có dữ liệu</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>📉 Top 15 Keywords Giảm Hạng</h3>
    <table autosize="1">
        <thead>
            <tr><th>Keyword</th><th class="text-right">Vị trí hiện tại</th><th class="text-right">Vị trí trước</th><th class="text-right">Thay đổi</th><th class="text-right">Volume</th></tr>
        </thead>
        <tbody>
            @forelse($losers as $l)
            <tr>
                <td>{{ is_array($l) ? ($l['keyword'] ?? '') : ($l->keyword ?? '') }}</td>
                <td class="text-right"><span class="badge badge-danger">{{ is_array($l) ? $l['current_position'] : $l->current_position }}</span></td>
                <td class="text-right text-muted">{{ is_array($l) ? ($l['previous_position'] ?? '—') : ($l->previous_position ?? '—') }}</td>
                <td class="text-right change-down">▼ {{ is_array($l) ? $l['position_change'] : $l->position_change }}</td>
                <td class="text-right">{{ number_format(is_array($l) ? ($l['search_volume'] ?? 0) : ($l->search_volume ?? 0)) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">Không có dữ liệu</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif

{{-- ══════════════════════════ TOP LANDING PAGES ═══════════════════════════ --}}
@if(in_array('landing_pages', $sections) && !empty($landingPages))
<div class="page-break section">
    <h2>Top Landing Pages (Top 10 Keywords)</h2>
    <table autosize="1">
        <thead>
            <tr><th>URL</th><th class="text-right">KWs</th><th class="text-right">Best Pos</th><th class="text-right">Avg Pos</th><th class="text-right">Volume</th></tr>
        </thead>
        <tbody>
            @foreach($landingPages as $page)
            <tr>
                <td style="word-break:break-all;">{{ is_array($page) ? ($page['target_url'] ?? '') : ($page->target_url ?? '') }}</td>
                <td class="text-right">{{ is_array($page) ? ($page['kw_count'] ?? 0) : ($page->kw_count ?? 0) }}</td>
                <td class="text-right">{{ is_array($page) ? ($page['best_pos'] ?? '—') : ($page->best_pos ?? '—') }}</td>
                <td class="text-right">{{ round(is_array($page) ? ($page['avg_pos'] ?? 0) : ($page->avg_pos ?? 0), 1) }}</td>
                <td class="text-right">{{ number_format(is_array($page) ? ($page['total_volume'] ?? 0) : ($page->total_volume ?? 0)) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ════════════════════════ COMPETITOR MONITORING ════════════════════════ --}}
@if(in_array('competitor_monitoring', $sections) && $competitorData && !empty($competitorData['domain_metrics']))
<div class="page-break section">
    <h2>Competitor Monitoring — Share of Voice</h2>

    @if(!empty($chartImages['sov_chart']))
    <div class="text-center" style="margin-bottom:10pt;">
        <img src="{{ $chartImages['sov_chart'] }}" style="max-width:60%; height:auto;" alt="Share of Voice">
    </div>
    @endif

    <table>
        <thead>
            <tr><th>Domain</th><th class="text-right">Total KWs</th><th class="text-right">Visibility</th><th class="text-right">Share of Voice</th><th class="text-right">Overlap</th></tr>
        </thead>
        <tbody>
            @foreach($competitorData['domain_metrics'] as $m)
            <tr>
                <td>
                    @if($m['is_main'])<strong>{{ $m['domain'] }} (Main)</strong>
                    @else{{ $m['domain'] }}@endif
                </td>
                <td class="text-right">{{ number_format($m['total_keywords']) }}</td>
                <td class="text-right">{{ number_format($m['visibility_score']) }}</td>
                <td class="text-right">
                    <strong>{{ $m['share_of_voice'] }}%</strong>
                </td>
                <td class="text-right">{{ number_format($m['overlap_with_main']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ══════════════════════════ ACTION ITEMS ════════════════════════════ --}}
@if(in_array('action_items', $sections))
<div class="page-break section">
    <h2>Gợi ý Hành động</h2>
    <table>
        <tbody>
            @if($kpis['declined'] > $kpis['improved'])
            <tr>
                <td style="width:20pt;">⚠️</td>
                <td>Số keywords giảm hạng ({{ $kpis['declined'] }}) nhiều hơn tăng hạng ({{ $kpis['improved'] }}). Cần audit nội dung các trang đang giảm.</td>
            </tr>
            @endif
            @if($kpis['top_3'] < 5)
            <tr>
                <td>🎯</td>
                <td>Chỉ có {{ $kpis['top_3'] }} keywords ở Top 3. Tập trung optimize các keywords ở vị trí 4-10 để đẩy vào Top 3.</td>
            </tr>
            @endif
            @if($kpis['lost_keywords'] > 0)
            <tr>
                <td>🔎</td>
                <td>Có {{ $kpis['lost_keywords'] }} keywords mới mất ranking. Kiểm tra lý do kỹ thuật (noindex, 404, cạnh tranh).</td>
            </tr>
            @endif
            @if($kpis['new_keywords'] > 0)
            <tr>
                <td>✅</td>
                <td>{{ $kpis['new_keywords'] }} keywords mới xuất hiện trong ranking. Theo dõi và tối ưu hóa các trang này.</td>
            </tr>
            @endif
            <tr>
                <td>📈</td>
                <td>Tiếp tục xây dựng internal links cho các trang trong Top 20 để cải thiện thứ hạng.</td>
            </tr>
        </tbody>
    </table>
</div>
@endif

</body>
</html>
