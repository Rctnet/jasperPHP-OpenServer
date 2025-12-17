<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report as ReportModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use JasperPHP\core\TJasper;
use JasperPHP\elements\Report;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->reports();

        // Apply filters
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        if ($request->has('directory_path')) {
            $query->where('directory_path', 'like', '%' . $request->directory_path . '%');
        }

        $reports = $query->with('dataSources')->paginate($request->input('per_page', 10));

        $reports->getCollection()->transform(function ($report) {
            $report->data_source_id = $report->dataSources->first()->id ?? null;
            unset($report->dataSources); // Remove the full dataSources object if not needed
            return $report;
        });

        return response()->json($reports);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:reports,slug',
            'description' => 'nullable|string',
            'report_file' => 'required|file|mimetypes:application/xml,text/xml,application/octet-stream|max:10240',
            'data_source_id' => 'required|exists:data_sources,id',
        ]);

        // 1. Get original file name
        $originalFileName = $request->file('report_file')->getClientOriginalName();
        Log::info("Original File Name: " . $originalFileName);

        // 2. Create report to get an ID (directory_path and main_jrxml_name are nullable now)
        $slug = $request->slug ?? \Illuminate\Support\Str::slug($request->name);
        // Ensure slug is unique if generated
        if (!$request->slug && ReportModel::where('slug', $slug)->exists()) {
             $slug .= '-' . uniqid();
        }

        $report = auth()->user()->reports()->create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'data_source_id' => $request->data_source_id, // Ensure data_source_id is also passed
        ]);
        Log::info("Report created with ID: " . $report->id);

        // 3. Define directory path using the newly created report ID
        $directoryPath = 'reports/user_' . auth()->id() . '/report_' . $report->id;
        Log::info("Directory Path: " . $directoryPath);

        // 4. Store the file with its original name
        $storedPath = $request->file('report_file')->storeAs($directoryPath, $originalFileName, 'private');
        Log::info("File stored at: " . $storedPath);

        // 5. Update report with path information
        $report->directory_path = $directoryPath;
        $report->main_jrxml_name = $originalFileName;
        $report->save();
        Log::info("Report updated with directory_path and main_jrxml_name.");

        $report->dataSources()->attach($request->data_source_id);

        return response()->json($report, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(ReportModel $report)
    {
        if ($report->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        return response()->json($report);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReportModel $report)
    {
        if ($report->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:reports,slug,' . $report->id,
            'description' => 'nullable|string',
            'report_file' => 'nullable|file|mimetypes:application/xml,text/xml,application/octet-stream|max:10240',
            'data_source_id' => 'required|exists:data_sources,id',
        ]);

        if ($request->hasFile('report_file')) {
            // Delete the old main report file
            Storage::disk('private')->delete($report->directory_path . '/' . $report->main_jrxml_name);

            // Store the new file and update the main_jrxml_name
            $newFileName = $request->file('report_file')->getClientOriginalName();
            $request->file('report_file')->storeAs($report->directory_path, $newFileName, 'private');
            $report->main_jrxml_name = $newFileName;
        }

        $report->name = $request->name;
        if ($request->has('slug') && $request->slug) {
             $report->slug = $request->slug;
        }
        $report->description = $request->description;
        $report->save();

        $report->dataSources()->sync($request->data_source_id);

        return response()->json($report);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReportModel $report)
    {
        if ($report->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        // Delete the entire report directory
        Storage::disk('private')->deleteDirectory($report->directory_path);
        $report->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Store a new subreport for a given report.
     */
    public function uploadSubreport(Request $request, ReportModel $report)
    {
        if ($report->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'subreport_file' => 'required|file|mimetypes:application/xml,text/xml,application/octet-stream|max:10240',
        ]);

        $file = $request->file('subreport_file');
        $originalFileName = $file->getClientOriginalName();

        // Store the subreport in the same directory as the main report
        $file->storeAs($report->directory_path, $originalFileName, 'private');

        return response()->json([
            'message' => 'Subreport uploaded successfully',
            'file_name' => $originalFileName,
        ], Response::HTTP_CREATED);
    }

    /**
     * Execute the specified report.
     */
    public function execute(Request $request)
    {
        $request->validate([
            'report_slug' => 'required_without:report_id|string|exists:reports,slug',
            'report_id' => 'required_without:report_slug|exists:reports,id',
            'data_source_slug' => 'nullable|string|exists:data_sources,slug',
            'data_source_id' => 'nullable|exists:data_sources,id',
            'format' => 'required|string|in:pdf,txt,xls,xlsx,docx,html',
            'parameters' => 'nullable|array',
            'json_data' => 'nullable|array',
            'debug_mode' => 'nullable|boolean'
        ]);

        if ($request->has('report_slug')) {
            $report = ReportModel::where('slug', $request->report_slug)->firstOrFail();
        } else {
            $report = ReportModel::find($request->report_id);
        }

        if ($report->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $inputFilePath = Storage::disk('private')->path($report->directory_path . '/' . $report->main_jrxml_name);
        $resourceDirectory = Storage::disk('private')->path($report->directory_path);

        $reportFormat = $request->format;
        $reportParameters = $request->parameters ?? [];
        $debugMode = $request->debug_mode ?? false;

        $reportParameters['type'] = $reportFormat;
        $dataSourceConfig = [
            'type' => 'array',
            'data' => [], // Default to empty array data source
        ];

        if ($request->data_source_slug) {
             $dataSource = \App\Models\DataSource::where('slug', $request->data_source_slug)->first();
        } elseif ($request->data_source_id) {
            $dataSource = \App\Models\DataSource::find($request->data_source_id);
        }

        if (isset($dataSource)) {
            if (!$dataSource || $dataSource->user_id !== auth()->id()) {
                return response()->json(['message' => 'Data Source Unauthorized or Not Found'], Response::HTTP_FORBIDDEN);
            }
            if ($dataSource->type === 'json' || $dataSource->type === 'array') {
                $dataSourceConfig = [
                    'type' => 'array',
                    'data' => $request->json_data ?? $dataSource->configuration,
                ];
            } else {
                $config = is_array($dataSource->configuration) ? $dataSource->configuration : [];
                $dataSourceConfig = [
                    'type' => 'db',
                    'db_driver' => $config['driver'] ?? null,
                    'db_host' => $config['host'] ?? null,
                    'db_port' => $config['port'] ?? null,
                    'db_name' => $config['database'] ?? null,
                    'db_user' => $config['username'] ?? null,
                    'db_pass' => $config['password'] ?? null,
                    'sql' => $report->sql_query, // Assuming report model has sql_query field
                ];
            }
        } else if ($request->json_data) {
            $dataSourceConfig = [
                'type' => 'array',
                'data' => $request->json_data,
            ];
        }

        // Dynamically set the resource folder for subreports
        $originalDefaultFolder = Report::$defaultFolder;
        Report::$defaultFolder = $resourceDirectory;

        try {
            $jasper = new TJasper($inputFilePath,$reportParameters, $dataSourceConfig,$debugMode);
            $reportContent = $jasper->output('S');

            $contentType = '';
            switch ($reportFormat) {
                case 'pdf':
                    $contentType = 'application/pdf';
                    break;
                case 'xls':
                    $contentType = 'application/vnd.ms-excel';
                    break;
                case 'xlsx':
                    $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                    break;
                case 'html':
                    $contentType = 'text/html';
                    break;
                case 'txt':
                    $contentType = 'text/plain';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    break;
            }

            return response($reportContent, 200)->header('Content-Type', $contentType);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Report generation failed', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } finally {
            // Restore the original default folder
            Report::$defaultFolder = $originalDefaultFolder;
        }
    }
}