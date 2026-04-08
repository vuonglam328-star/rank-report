<?php

namespace App\Services;

use App\Models\GeneratedReport;
use App\Models\Snapshot;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

/**
 * PdfReportService
 *
 * Renders a GeneratedReport into a PDF file using mPDF.
 * Charts are passed as base64 PNG strings (captured by Chart.js in browser).
 */
class PdfReportService
{
    public function __construct(
        private KpiCalculatorService      $kpiService,
        private TimelineService           $timelineService,
        private CompetitorAnalysisService $competitorService
    ) {}

    /**
     * Generate PDF and save to storage.
     *
     * @param  GeneratedReport  $report
     * @param  array            $chartImages  ['chart_id' => 'data:image/png;base64,...']
     * @return GeneratedReport  Updated report with pdf_path
     */
    public function generate(GeneratedReport $report, array $chartImages = []): GeneratedReport
    {
        $report->update(['status' => 'generating']);

        try {
            $snapshot  = $report->snapshot;
            $project   = $report->project;
            $template  = $report->template;
            $sections  = $report->selected_sections_json ?? $template->getDefaultSections();
            $competitorIds = collect($report->selected_competitors_json ?? [])
                ->pluck('project_id')
                ->toArray();

            // ── Collect all data for report ───────────────────────────────────
            $kpis = $this->kpiService->calculate($snapshot);
            $prevSnapshot = $snapshot->previousSnapshot();
            $prevKpis = $prevSnapshot ? $this->kpiService->calculate($prevSnapshot) : null;
            $winners  = $this->kpiService->getTopWinners($snapshot, 15);
            $losers   = $this->kpiService->getTopLosers($snapshot, 15);
            $pages    = $this->kpiService->getTopLandingPages($snapshot, 10);

            $competitorData = null;
            if (!empty($competitorIds)) {
                $competitorData = $this->competitorService->analyze(
                    $project, $competitorIds, $snapshot->report_date->format('Y-m-d')
                );
            }

            // ── Render HTML ───────────────────────────────────────────────────
            $html = view('reports.pdf-template', [
                'report'         => $report,
                'project'        => $project,
                'snapshot'       => $snapshot,
                'template'       => $template,
                'sections'       => $sections,
                'kpis'           => $kpis,
                'prevKpis'       => $prevKpis,
                'winners'        => $winners,
                'losers'         => $losers,
                'landingPages'   => $pages,
                'competitorData' => $competitorData,
                'chartImages'    => $chartImages,
            ])->render();

            // ── Create mPDF instance ──────────────────────────────────────────
            $mpdf = $this->createMpdfInstance($template);

            // Set header and footer
            $mpdf->SetHTMLHeader('
                <table style="width:100%; border-bottom:1px solid #ddd; padding-bottom:5px;">
                    <tr>
                        <td style="font-size:9px; color:#999;">' . ($project->client->company_name ?? $project->client->name) . '</td>
                        <td style="font-size:9px; color:#999; text-align:right;">' . ($template->agency_name ?? 'RankReport Pro') . '</td>
                    </tr>
                </table>
            ');

            $mpdf->SetHTMLFooter('
                <div style="text-align:center; font-size:9px; color:#aaa; border-top:1px solid #eee; padding-top:4px;">
                    Page {PAGENO} of {nbpg} &nbsp;|&nbsp; ' . $report->report_title . '
                </div>
            ');

            $mpdf->WriteHTML($html);

            // ── Save PDF ──────────────────────────────────────────────────────
            $filename = Str::slug($report->report_title) . '_' . now()->format('Y-m-d_His') . '.pdf';
            $pdfPath  = config('rankreport.storage.pdf_path') . '/' . $filename;

            Storage::put($pdfPath, $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN));

            $report->update([
                'status'    => 'ready',
                'pdf_path'  => $pdfPath,
            ]);

        } catch (\Throwable $e) {
            $report->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $report->fresh();
    }

    /**
     * Stream PDF to browser for download.
     */
    public function download(GeneratedReport $report): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (!$report->pdf_path || !Storage::exists($report->pdf_path)) {
            abort(404, 'PDF not found. Please regenerate the report.');
        }

        $filename = Str::slug($report->report_title) . '.pdf';

        return Storage::download($report->pdf_path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    public function createMpdfInstance($template): Mpdf
    {
        $tmpDir = storage_path('app/mpdf_tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'mode'           => 'utf-8',
            'format'         => 'A4',
            'orientation'    => 'P',
            'margin_top'     => 22,
            'margin_left'    => 15,
            'margin_right'   => 15,
            'margin_bottom'  => 20,
            'margin_header'  => 10,
            'margin_footer'  => 10,
            'tempDir'        => $tmpDir,
            'default_font'   => 'dejavusans', // Unicode-safe, supports Vietnamese
            'autoPageBreak'  => true,
        ]);

        return $mpdf;
    }
}
