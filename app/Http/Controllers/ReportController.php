<?php

namespace App\Http\Controllers;

use App\Models\GeneratedReport;
use App\Models\Keyword;
use App\Models\Project;
use App\Models\ReportTemplate;
use App\Models\Snapshot;
use App\Services\CompetitorAnalysisService;
use App\Services\KpiCalculatorService;
use App\Services\PdfReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private PdfReportService        $pdfService,
        private KpiCalculatorService    $kpiService,
        private CompetitorAnalysisService $competitorService,
    ) {}

    /**
     * Tạo PDF filtered theo URL hoặc keyword đã chọn (từ dashboard).
     * POST /reports/filtered
     */
    public function storeFiltered(Request $request)
    {
        $validated = $request->validate([
            'project_id'          => 'required|exists:projects,id',
            'snapshot_id'         => 'required|exists:snapshots,id',
            'report_template_id'  => 'nullable|exists:report_templates,id',
            'report_title'        => 'required|string|max:255',
            'filter_type'         => 'required|in:urls,keywords',
            'selected_urls'       => 'nullable|array',
            'selected_urls.*'     => 'string',
            'selected_keyword_ids'=> 'nullable|array',
            'selected_keyword_ids.*' => 'integer',
            'include_competitors' => 'nullable|boolean',
            'competitor_ids'      => 'nullable|array',
            'competitor_ids.*'    => 'exists:projects,id',
        ]);

        $snapshot = \App\Models\Snapshot::findOrFail($validated['snapshot_id']);
        $project  = Project::findOrFail($validated['project_id']);
        $template = \App\Models\ReportTemplate::when(
                        $validated['report_template_id'] ?? null,
                        fn($q, $id) => $q->where('id', $id),
                        fn($q) => $q->where('is_default', true)
                    )->first()
                    ?? \App\Models\ReportTemplate::first();

        // ── Resolve keyword_ids từ URLs hoặc trực tiếp ───────────────────────
        if ($validated['filter_type'] === 'urls' && !empty($validated['selected_urls'])) {
            $keywordIds = \Illuminate\Support\Facades\DB::table('keyword_rankings')
                ->where('snapshot_id', $snapshot->id)
                ->whereIn('target_url', $validated['selected_urls'])
                ->pluck('keyword_id')
                ->unique()
                ->values()
                ->toArray();
        } else {
            $keywordIds = array_map('intval', $validated['selected_keyword_ids'] ?? []);
        }

        if (empty($keywordIds)) {
            return back()->withErrors(['filter' => 'Không có keyword nào được chọn.']);
        }

        // ── KPI cho filtered keywords ─────────────────────────────────────────
        $kpis    = $this->kpiService->calculateForKeywordIds($snapshot, $keywordIds);
        $winners = $this->kpiService->getTopWinnersForKeywordIds($snapshot, $keywordIds, 15);
        $losers  = $this->kpiService->getTopLosersForKeywordIds($snapshot, $keywordIds, 15);

        // Top landing pages cho filtered keywords
        $landingPages = \Illuminate\Support\Facades\DB::table('keyword_rankings')
            ->where('snapshot_id', $snapshot->id)
            ->whereIn('keyword_id', $keywordIds)
            ->whereNotNull('target_url')
            ->groupBy('target_url')
            ->orderByDesc('kw_count')
            ->selectRaw('target_url, COUNT(*) as kw_count, MIN(current_position) as best_pos, AVG(current_position) as avg_pos, SUM(search_volume) as total_volume')
            ->get()->toArray();

        // ── Competitor analysis (nếu chọn include) ────────────────────────────
        $competitorData = null;
        $competitorIds  = $validated['competitor_ids'] ?? [];
        if (!empty($competitorIds) && ($validated['include_competitors'] ?? false)) {
            $competitorData = $this->competitorService->analyze(
                $project,
                $competitorIds,
                $snapshot->report_date->format('Y-m-d'),
                $keywordIds   // ← chỉ phân tích trên tập keywords được chọn
            );
        }

        // ── Render + generate PDF ─────────────────────────────────────────────
        $filterLabel = $validated['filter_type'] === 'urls'
            ? count($validated['selected_urls'] ?? []) . ' URLs'
            : count($keywordIds) . ' keywords';

        $sections = ['cover','kpi_summary','top_keywords','landing_pages'];
        if ($competitorData) $sections[] = 'competitor_monitoring';

        $selectedCompetitors = [];
        foreach ($competitorIds as $cid) {
            $p = Project::find($cid);
            if ($p) $selectedCompetitors[] = ['project_id' => (int)$cid, 'label' => $p->domain_clean];
        }

        $report = GeneratedReport::create([
            'project_id'               => $project->id,
            'snapshot_id'              => $snapshot->id,
            'report_template_id'       => $template?->id,
            'report_title'             => $validated['report_title'],
            'summary_text'             => "Báo cáo lọc theo {$filterLabel}.",
            'selected_sections_json'   => $sections,
            'selected_competitors_json'=> $selectedCompetitors,
            'status'                   => 'pending',
        ]);

        try {
            // Render PDF với data đã tính sẵn (bypass generate() thông thường)
            $report->update(['status' => 'generating']);

            $html = view('reports.pdf-template', [
                'report'         => $report,
                'project'        => $project,
                'snapshot'       => $snapshot,
                'template'       => $template,
                'sections'       => $sections,
                'kpis'           => $kpis,
                'prevKpis'       => null,
                'winners'        => $winners,
                'losers'         => $losers,
                'landingPages'   => $landingPages,
                'competitorData' => $competitorData,
                'chartImages'    => [],
            ])->render();

            $mpdf = $this->pdfService->createMpdfInstance($template);
            $mpdf->SetHTMLFooter('<div style="text-align:center;font-size:9px;color:#aaa;border-top:1px solid #eee;padding-top:4px;">Page {PAGENO} of {nbpg} &nbsp;|&nbsp; ' . $report->report_title . '</div>');
            $mpdf->WriteHTML($html);

            $filename = \Illuminate\Support\Str::slug($report->report_title) . '_' . now()->format('Y-m-d_His') . '.pdf';
            $pdfPath  = 'reports/pdf/' . $filename;
            \Illuminate\Support\Facades\Storage::put($pdfPath, $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN));

            $report->update(['status' => 'ready', 'pdf_path' => $pdfPath]);

            return redirect()->route('reports.index')
                ->with('success', "Báo cáo \"{$report->report_title}\" đã tạo thành công.");

        } catch (\Throwable $e) {
            $report->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            return back()->withErrors(['generate' => 'Tạo PDF thất bại: ' . $e->getMessage()]);
        }
    }

    public function index()
    {
        $reports = GeneratedReport::with(['project.client', 'snapshot', 'template'])
            ->latest()
            ->paginate(20);

        return view('reports.index', compact('reports'));
    }

    public function create(Request $request)
    {
        $projects  = Project::with('client')
            ->where('project_type', 'main')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $templates = ReportTemplate::orderByDesc('is_default')->orderBy('name')->get();

        $selectedProject  = $request->filled('project_id') ? Project::find($request->project_id) : null;
        $selectedSnapshot = null;
        $snapshots        = collect();
        $competitors      = collect();

        if ($selectedProject) {
            $snapshots   = $selectedProject->snapshots()->where('status', 'completed')->orderByDesc('report_date')->get();
            $competitors = $selectedProject->competitors()->where('status', 'active')->get(['projects.id', 'projects.name', 'projects.domain']);

            if ($request->filled('snapshot_id')) {
                $selectedSnapshot = Snapshot::find($request->snapshot_id);
            } else {
                $selectedSnapshot = $selectedProject->latestSnapshot;
            }
        }

        $availableSections = [
            'cover'                => 'Trang bìa',
            'executive_summary'    => 'Executive Summary',
            'kpi_summary'          => 'KPI Summary',
            'position_chart'       => 'Biểu đồ xu hướng vị trí',
            'distribution_chart'   => 'Biểu đồ phân bổ nhóm vị trí',
            'top_keywords'         => 'Top Keywords (tăng/giảm)',
            'landing_pages'        => 'Top Landing Pages',
            'competitor_monitoring'=> 'Competitor Monitoring',
            'action_items'         => 'Gợi ý hành động',
        ];

        return view('reports.create', compact(
            'projects', 'templates', 'snapshots',
            'selectedProject', 'selectedSnapshot', 'competitors',
            'availableSections'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'         => 'required|exists:projects,id',
            'snapshot_id'        => 'required|exists:snapshots,id',
            'report_template_id' => 'required|exists:report_templates,id',
            'report_title'       => 'required|string|max:255',
            'summary_text'       => 'nullable|string|max:5000',
            'selected_sections'  => 'required|array|min:1',
            'selected_sections.*'=> 'string',
            'competitor_ids'     => 'nullable|array',
            'competitor_ids.*'   => 'exists:projects,id',
            'chart_images'       => 'nullable|string', // JSON of base64 images from browser
        ]);

        // Build competitor JSON
        $competitorIds = $validated['competitor_ids'] ?? [];
        $selectedCompetitors = [];
        foreach ($competitorIds as $cid) {
            $proj = Project::find($cid);
            if ($proj) {
                $selectedCompetitors[] = ['project_id' => (int)$cid, 'label' => $proj->domain_clean];
            }
        }

        // Parse chart images from browser
        $chartImages = [];
        if (!empty($validated['chart_images'])) {
            $chartImages = json_decode($validated['chart_images'], true) ?? [];
        }

        // Create report record
        $report = GeneratedReport::create([
            'project_id'               => $validated['project_id'],
            'snapshot_id'              => $validated['snapshot_id'],
            'report_template_id'       => $validated['report_template_id'],
            'report_title'             => $validated['report_title'],
            'summary_text'             => $validated['summary_text'] ?? null,
            'selected_sections_json'   => $validated['selected_sections'],
            'selected_competitors_json'=> $selectedCompetitors,
            'status'                   => 'pending',
        ]);

        try {
            // Generate PDF synchronously (for MVP; use Job for large files)
            $report = $this->pdfService->generate($report, $chartImages);

            return redirect()
                ->route('reports.index')
                ->with('success', "Báo cáo \"{$report->report_title}\" đã được tạo thành công.");

        } catch (\Throwable $e) {
            return back()
                ->withErrors(['generate' => 'Tạo PDF thất bại: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function download(GeneratedReport $report)
    {
        return $this->pdfService->download($report);
    }

    public function destroy(GeneratedReport $report)
    {
        $title = $report->report_title;
        if ($report->pdf_path) {
            \Illuminate\Support\Facades\Storage::delete($report->pdf_path);
        }
        $report->delete();

        return redirect()
            ->route('reports.index')
            ->with('success', "Báo cáo \"{$title}\" đã được xóa.");
    }
}
