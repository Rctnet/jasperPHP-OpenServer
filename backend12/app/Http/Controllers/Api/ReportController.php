<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use JasperPHP\core\TJasper;

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

        if ($request->has('file_path')) {
            $query->where('file_path', 'like', '%' . $request->file_path . '%');
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
            'description' => 'nullable|string',
            'report_file' => 'required|file|mimetypes:application/xml,text/xml,application/octet-stream|max:10240',
            'data_source_id' => 'required|exists:data_sources,id',
        ]);

        $filePath = $request->file('report_file')->store('reports', 'private');

        $report = auth()->user()->reports()->create([
            'name' => $request->name,
            'description' => $request->description,
            'file_path' => $filePath,
        ]);

        $report->dataSources()->attach($request->data_source_id);

        return response()->json($report, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        if ($report->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        return response()->json($report);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        if ($report->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'report_file' => 'nullable|file|mimetypes:application/xml,text/xml,application/octet-stream|max:10240',
            'data_source_id' => 'required|exists:data_sources,id',
        ]);

        if ($request->hasFile('report_file')) {
            Storage::disk('private')->delete($report->file_path);
            $filePath = $request->file('report_file')->store('reports', 'private');
            $report->file_path = $filePath;
        }

        $report->update($request->only(['name', 'description']));
        $report->dataSources()->sync($request->data_source_id);

        return response()->json($report);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        if ($report->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        Storage::disk('private')->delete($report->file_path);
        $report->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Execute the specified report.
     */
    public function execute(Request $request)
    {
        $request->validate([
            'report_id' => 'required|exists:reports,id',
            'data_source_id' => 'nullable|exists:data_sources,id',
            'format' => 'required|string|in:pdf,txt,xls,xlsx,docx,html',
            'parameters' => 'nullable|array',
            'json_data' => 'nullable|array',
            'debug_mode' => 'nullable|boolean'
        ]);

        $report = Report::find($request->report_id);

        if ($report->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $inputFilePath = Storage::disk('private')->path($report->file_path);
        $reportFormat = $request->format;
        $reportParameters = $request->parameters ?? [];
        $debugMode = $request->debug_mode ?? false;

        $reportParameters['type'] = $reportFormat;
        $dataSourceConfig = [
            'type' => 'array',
            'data' => [], // Default to empty array data source
        ];

        if ($request->data_source_id) {
            $dataSource = \App\Models\DataSource::find($request->data_source_id);
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

        // try {
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
        // } catch (\Exception $e) {
        //     return response()->json(['message' => 'Report generation failed', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        // }
    }
}