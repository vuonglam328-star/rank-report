<?php

namespace App\Http\Controllers;

use App\Exceptions\DuplicateSnapshotException;
use App\Models\Project;
use App\Models\Snapshot;
use App\Services\CsvParserService;
use App\Services\SnapshotImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ImportController extends Controller
{
    public function __construct(
        private CsvParserService      $csvParser,
        private SnapshotImportService $importer
    ) {}

    /**
     * Import history list
     */
    public function index(Request $request)
    {
        $query = Snapshot::with(['project.client'])
            ->orderByDesc('report_date');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $snapshots = $query->paginate(25)->withQueryString();
        $projects  = Project::with('client')->orderBy('name')->get(['id', 'name', 'domain', 'client_id']);

        return view('imports.index', compact('snapshots', 'projects'));
    }

    /**
     * Upload form
     */
    public function create(Request $request)
    {
        $projects = Project::with('client')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $selectedProject = $request->filled('project_id')
            ? Project::find($request->project_id)
            : null;

        return view('imports.create', compact('projects', 'selectedProject'));
    }

    /**
     * Step 1: Upload CSV(s) and detect columns
     * POST /imports/upload
     */
    public function upload(Request $request)
    {
        $maxKb = config('rankreport.max_csv_size_mb') * 1024;
        $request->validate([
            'project_id'    => 'required|exists:projects,id',
            'report_date'   => 'required|date',
            'csv_files'     => 'required|array|min:1',
            'csv_files.*'   => "required|file|mimes:csv,txt|max:{$maxKb}",
            'notes'         => 'nullable|string|max:1000',
        ]);

        $project = Project::findOrFail($request->project_id);

        // Check for duplicate snapshot date before storing file
        $existing = Snapshot::where('project_id', $project->id)
            ->where('report_date', $request->report_date)
            ->first();

        // Store all CSV files to disk
        $storagePaths = [];
        foreach ($request->file('csv_files') as $file) {
            $storagePaths[] = $this->csvParser->storeUpload($file, $project->id);
        }

        // Detect columns from the first file
        $detection = $this->csvParser->detectColumns($storagePaths[0]);

        // Store context in session for next step
        Session::put('import_context', [
            'project_id'    => $project->id,
            'report_date'   => $request->report_date,
            'notes'         => $request->notes,
            'storage_paths' => $storagePaths,
            'auto_map'      => $detection['auto_map'],
        ]);

        return view('imports.map-columns', [
            'project'          => $project,
            'reportDate'       => $request->report_date,
            'csvHeaders'       => $detection['headers'],
            'autoMap'          => $detection['auto_map'],
            'existingSnapshot' => $existing,
            'storagePaths'     => $storagePaths,
            'fileCount'        => count($storagePaths),
        ]);
    }

    /**
     * Validate that a storage path is within the allowed CSV directory.
     * Prevents path traversal attacks (e.g. ../../.env).
     */
    private function validateStoragePath(string $path, int $projectId): void
    {
        // Only allow safe characters: letters, digits, hyphens, underscores, dots, forward slashes
        if (!preg_match('#^[a-zA-Z0-9/_\-\.]+$#', $path)) {
            abort(422, 'Invalid file path.');
        }

        // Resolve absolute paths and ensure the file stays inside the allowed directory
        $allowedBase = realpath(storage_path('app/imports/csv/' . $projectId));
        $absolute    = realpath(storage_path('app/' . $path));

        if (!$absolute || !$allowedBase || !str_starts_with($absolute, $allowedBase)) {
            abort(422, 'File path is outside allowed directory.');
        }
    }

    /**
     * Step 2: Preview mapped data
     * POST /imports/preview
     */
    public function preview(Request $request)
    {
        $request->validate([
            'project_id'      => 'required|exists:projects,id',
            'report_date'     => 'required|date|before_or_equal:today|after:2000-01-01',
            'storage_paths'   => 'required|array|min:1|max:20',
            'storage_paths.*' => 'required|string|max:300',
            'column_map'      => 'required|array',
        ]);

        $storagePaths = $request->storage_paths;

        // Prevent path traversal
        foreach ($storagePaths as $path) {
            $this->validateStoragePath($path, $request->project_id);
        }
        $columnMap    = $request->column_map;

        // Update session with confirmed column map
        $context = Session::get('import_context', []);
        $context['column_map']    = $columnMap;
        $context['storage_paths'] = $storagePaths;
        Session::put('import_context', $context);

        // Preview from first file only (for display purposes)
        $preview = $this->csvParser->previewRows(
            $storagePaths[0],
            $columnMap,
            config('rankreport.preview_rows_limit', 20)
        );

        // Count total rows across ALL files
        $totalRows = 0;
        foreach ($storagePaths as $path) {
            $counted = $this->csvParser->countRows($path, $columnMap);
            $totalRows += $counted;
        }

        $project = Project::findOrFail($request->project_id);

        return view('imports.preview', [
            'project'      => $project,
            'reportDate'   => $request->report_date,
            'storagePaths' => $storagePaths,
            'columnMap'    => $columnMap,
            'preview'      => $preview,
            'totalRows'    => $totalRows,
            'fileCount'    => count($storagePaths),
        ]);
    }

    /**
     * Step 3: Confirm and run import
     * POST /imports/confirm
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'project_id'      => 'required|exists:projects,id',
            'report_date'     => 'required|date|before_or_equal:today|after:2000-01-01',
            'storage_paths'   => 'required|array|min:1|max:20',
            'storage_paths.*' => 'required|string|max:300',
            'column_map'      => 'required|array',
            'force_overwrite' => 'boolean',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $project        = Project::findOrFail($request->project_id);
        $columnMap      = $request->column_map;
        $storagePaths   = $request->storage_paths;
        $forceOverwrite = $request->boolean('force_overwrite');

        // Prevent path traversal
        foreach ($storagePaths as $path) {
            $this->validateStoragePath($path, $project->id);
        }

        // Parse & merge all files
        $allRows = [];
        $seenNormalized = [];
        foreach ($storagePaths as $path) {
            $parsed = $this->csvParser->parse($path, $columnMap);
            foreach ($parsed['rows'] as $row) {
                $key = $row['normalized_keyword'];
                if (!isset($seenNormalized[$key])) {
                    $seenNormalized[$key] = true;
                    $allRows[] = $row;
                }
            }
        }

        // Helper: re-render preview view with a message
        $renderPreview = function (array $extra) use ($project, $request, $storagePaths, $columnMap) {
            $preview = $this->csvParser->previewRows(
                $storagePaths[0],
                $columnMap,
                config('rankreport.preview_rows_limit', 20)
            );
            $totalRows = 0;
            foreach ($storagePaths as $path) {
                $totalRows += $this->csvParser->countRows($path, $columnMap);
            }
            return view('imports.preview', array_merge([
                'project'      => $project,
                'reportDate'   => $request->report_date,
                'storagePaths' => $storagePaths,
                'columnMap'    => $columnMap,
                'preview'      => $preview,
                'totalRows'    => $totalRows,
                'fileCount'    => count($storagePaths),
            ], $extra));
        };

        if (empty($allRows)) {
            return $renderPreview(['importError' => 'Các file CSV không có dữ liệu hợp lệ. Kiểm tra lại mapping cột.']);
        }

        $meta = [
            'snapshot_name'    => "Ahrefs Export – {$project->domain} ({$request->report_date})",
            'report_date'      => $request->report_date,
            'snapshot_type'    => 'ahrefs',
            'source_file_path' => $storagePaths[0],
            'notes'            => $request->notes,
        ];

        try {
            $snapshot = $this->importer->import($project, $allRows, $meta, $forceOverwrite);

            Session::forget('import_context');

            return redirect()
                ->route('imports.show', $snapshot)
                ->with('success', "Import thành công: {$snapshot->total_keywords} keywords từ " . count($storagePaths) . " file cho {$project->domain} ngày {$request->report_date}.");

        } catch (DuplicateSnapshotException $e) {
            return $renderPreview(['duplicateSnapshot' => [
                'existing_id'   => $e->existingSnapshot->id,
                'existing_date' => $e->existingSnapshot->report_date->format('d/m/Y'),
                'message'       => $e->getMessage(),
            ]]);
        } catch (\Throwable $e) {
            return $renderPreview(['importError' => 'Import thất bại: ' . $e->getMessage()]);
        }
    }

    /**
     * Show a single snapshot detail
     */
    public function show(Snapshot $snapshot)
    {
        $snapshot->load('project.client');
        $topRankings = $snapshot->keywordRankings()
            ->with('keyword')
            ->whereNotNull('current_position')
            ->orderBy('current_position')
            ->take(20)
            ->get();

        return view('imports.show', compact('snapshot', 'topRankings'));
    }

    /**
     * Delete a snapshot
     */
    public function destroy(Snapshot $snapshot)
    {
        $projectId = $snapshot->project_id;
        $date = $snapshot->report_date->format('d/m/Y');
        $snapshot->keywordRankings()->delete();
        $snapshot->delete();

        return redirect()
            ->route('imports.index', ['project_id' => $projectId])
            ->with('success', "Snapshot ngày {$date} đã được xóa.");
    }

    /**
     * API: Get snapshots for a project (JSON for AJAX selects).
     */
    public function snapshotsByProject(Project $project)
    {
        $snapshots = $project->snapshots()
            ->where('status', 'completed')
            ->orderByDesc('report_date')
            ->get(['id', 'snapshot_name', 'report_date', 'total_keywords']);

        return response()->json($snapshots);
    }
}
