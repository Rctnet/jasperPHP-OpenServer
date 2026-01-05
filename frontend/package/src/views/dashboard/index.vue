<template>
  <v-card>
    <v-card-title class="text-h5">Complete Guide to JasperPHP API (PHP/Laravel with Guzzle)</v-card-title>
    <v-card-text>
      <p>This guide demonstrates how to interact with the JasperPHP API using PHP, focusing on Laravel projects and the Guzzle library for HTTP requests.</p>

      <h3 class="text-h6 mt-4">Prerequisites:</h3>
      <ul>
        <li>Laravel installed and configured.</li>
        <li>Guzzle HTTP Client installed: <code>composer require guzzlehttp/guzzle</code></li>
      </ul>

      <h3 class="text-h6 mt-4">Basic Guzzle Configuration:</h3>
      <p>To configure Guzzle in Laravel, you can create a service or use it directly in your controllers. Creating a service is recommended for better organization and reusability.</p>

      <h4 class="text-h6 mt-4">1. Creating a Guzzle Service (Optional, but Recommended):</h4>
      <p>Create a new file in <code>app/Services/JasperApiService.php</code> (or similar):</p>
      <CodeBlock language="php" :code="guzzleServiceCode" label="app/Services/JasperApiService.php" theme="neon-bunny" />

      <h4 class="text-h6 mt-4">2. Example Usage in Controller:</h4>
      <p>You can inject the service into your controller or create a Guzzle instance directly:</p>
      <CodeBlock language="php" :code="controllerUsageCode" label="Controller Example" theme="neon-bunny" />

      <h3 class="text-h6 mt-4">API Usage Example:</h3>
      <p>The JasperPHP API typically involves sending POST requests to generate reports. Common parameters include the report name, output format, and data to populate the report.</p>

      <h4 class="text-h6 mt-4">1. Generating a Simple Report:</h4>
      <p>This example demonstrates how to generate a simple PDF report without additional parameters:</p>
      <CodeBlock language="php" :code="simpleReportCode" label="Generate Simple Report" theme="neon-bunny" />

      <h4 class="text-h6 mt-4">2. Generating a Report with Parameters:</h4>
      <p>For reports that require data, you can pass them in the request body:</p>
      <CodeBlock language="php" :code="reportWithParamsCode" label="Generate Report with Parameters" theme="neon-bunny" />

      <h3 class="text-h6 mt-4">Error Handling:</h3>
      <p>It is crucial to implement error handling to deal with API communication failures or unexpected responses:</p>
      <CodeBlock language="php" :code="errorHandlingCode" label="Tratamento de Erros" theme="neon-bunny" />

      <h3 class="text-h6 mt-4">Complete Example:</h3>
      <p>Here is a more complete example of how to integrate JasperPHP report generation into a Laravel controller:</p>
      <CodeBlock language="php" :code="fullExampleCode" label="Complete Controller Example" theme="neon-bunny" />
    </v-card-text>
  </v-card>
</template>


<script setup lang="ts">
import { ref } from 'vue';
import CodeBlock from '@/components/CodeBlock.vue';

const guzzleServiceCode = ref(`<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class JasperApiService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'http://your-jasperphp-server.com/api/',
            'headers' => [
                'Accept' => 'application/json',
                // 'Authorization' => 'Bearer YOUR_API_TOKEN',
            ],
        ]);
    }

    public function generateReport(string $slug, string $format, array $parameters = [])
    {
        try {
            $response = $this->client->post("reports/execute", [
                'json' => [
                    'report_slug' => $slug,
                    'format' => $format,
                    'parameters' => $parameters,
                ],
            ]);

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return $e->getResponse()->getBody()->getContents();
            }
            return $e->getMessage();
        }
    }
}
`);

const controllerUsageCode = ref(`<?php

namespace App\Http\Controllers;

use App\Services\JasperApiService;
use Illuminate\Http\Request;
use GuzzleHttp\Client; // Only if you're not using the service

class ReportController extends Controller
{
    protected $jasperApiService;

    public function __construct(JasperApiService $jasperApiService)
    {
        $this->jasperApiService = $jasperApiService;
    }

    public function generateSimpleReport()
    {
        $reportPath = '/reports/samples/Employees';
        $format = 'pdf';

        $reportContent = $this->jasperApiService->generateReport($reportPath, $format);

        return response($reportContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="employees.pdf"');
    }

    public function generateReportWithDirectGuzzle(Request $request)
    {
        $client = new Client([
            'base_uri' => 'http://your-jasperphp-server.com/api/',
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer YOUR_API_TOKEN',
            ],
        ]);

        try {
            $response = $client->post("reports/execute", [
                'json' => [
                    'report_slug' => 'my-report-slug',
                    'format' => 'pdf',
                    'parameters' => [
                         'customer_id' => $request->input('customer_id'),
                    ]
                ],
            ]);

            return response($response->getBody()->getContents())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="customer_report.pdf"');
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                return response($e->getResponse()->getBody()->getContents(), $e->getResponse()->getStatusCode());
            }
            return response($e->getMessage(), 500);
        }
    }
}
`);

const simpleReportCode = ref(`<?php

namespace App\Http\Controllers;

use App\Services\JasperApiService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $jasperApiService;

    public function __construct(JasperApiService $jasperApiService)
    {
        $this->jasperApiService = $jasperApiService;
    }

    public function generateSimpleReport()
    {
        $reportSlug = 'employees';
        $format = 'pdf';

        $reportContent = $this->jasperApiService->generateReport($reportSlug, $format);

        return response($reportContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="employees.pdf"');
    }
}
`);

const reportWithParamsCode = ref(`<?php

namespace App\Http\Controllers;

use App\Services\JasperApiService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $jasperApiService;

    public function __construct(JasperApiService $jasperApiService)
    {
        $this->jasperApiService = $jasperApiService;
    }

    public function generateReportWithParams(Request $request)
    {
        $reportSlug = 'customers';
        $format = 'pdf';
        $parameters = [
            'CustomerID' => $request->input('customer_id'),
            'StartDate' => '2023-01-01',
            'EndDate' => '2023-12-31',
        ];

        $reportContent = $this->jasperApiService->generateReport($reportSlug, $format, $parameters);

        return response($reportContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="customer_report.pdf"');
    }
}
`);

const errorHandlingCode = ref(`<?php

namespace App\Http\Controllers;

use App\Services\JasperApiService;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;

class ReportController extends Controller
{
    protected $jasperApiService;

    public function __construct(JasperApiService $jasperApiService)
    {
        $this->jasperApiService = $jasperApiService;
    }

    public function generateReportWithErrorHandling(Request $request)
    {
        $reportSlug = 'non-existent-report'; // Example of a non-existent report
        $format = 'pdf';
        $parameters = [];

        try {
            $reportContent = $this->jasperApiService->generateReport($reportSlug, $format, $parameters);

            // Check if the content is an error message from the service
            if (is_string($reportContent) && (str_contains($reportContent, 'error') || str_contains($reportContent, 'Error') || str_contains($reportContent, 'faultCode'))) {
                // Attempt to decode as JSON if it looks like an error response
                $decodedContent = json_decode($reportContent, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decodedContent['error'])) {
                    return response()->json(['error' => 'Jasper API Error', 'details' => $decodedContent['error']], 500);
                }
                return response()->json(['error' => 'Failed to generate report', 'details' => $reportContent], 500);
            }

            return response($reportContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="report.pdf"');

        } catch (RequestException $e) {
            // Guzzle HTTP client exceptions
            $statusCode = 500;
            $errorMessage = 'Network error or communication issue with Jasper API.';
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                $errorMessage = $e->getResponse()->getBody()->getContents();
            }
            return response()->json(['error' => 'API request failed', 'details' => $errorMessage], $statusCode);
        } catch (\Exception $e) {
            // Other general exceptions
            return response()->json(['error' => 'An unexpected error occurred', 'details' => $e->getMessage()], 500);
        }
    }
}
`);

const fullExampleCode = ref(`<?php

namespace App\Http\Controllers;

use App\Services\JasperApiService;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;

class FullReportController extends Controller
{
    protected $jasperApiService;

    public function __construct(JasperApiService $jasperApiService)
    {
        $this->jasperApiService = $jasperApiService;
    }

    /**
     * Handle report generation request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        $reportSlug = $request->input('report_slug', 'employees');
        $format = $request->input('format', 'pdf');
        $parameters = $request->input('parameters', []);

        try {
            $reportContent = $this->jasperApiService->generateReport($reportSlug, $format, $parameters);

            // Basic check if the returned content is likely an error message from Jasper
            if (is_string($reportContent) && (str_contains($reportContent, 'error') || str_contains($reportContent, 'Error') || str_contains($reportContent, 'faultCode'))) {
                // Attempt to decode as JSON if it looks like an error response
                $decodedContent = json_decode($reportContent, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decodedContent['error'])) {
                    return response()->json(['error' => 'Jasper API Error', 'details' => $decodedContent['error']], 500);
                }
                return response()->json(['error' => 'Failed to generate report', 'details' => $reportContent], 500);
            }

            // Determine content type for the response
            $contentType = 'application/' . $format;
            if ($format === 'html') {
                $contentType = 'text/html';
            } elseif ($format === 'csv') {
                $contentType = 'text/csv';
            }

            return response($reportContent)
                ->header('Content-Type', $contentType)
                ->header('Content-Disposition', 'inline; filename="report.' . $format . '"');

        } catch (RequestException $e) {
            $statusCode = 500;
            $errorMessage = 'Network error or communication issue with Jasper API.';
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                $errorMessage = $e->getResponse()->getBody()->getContents();
            }
            return response()->json(['error' => 'API request failed', 'details' => $errorMessage], $statusCode);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'details' => $e->getMessage()], 500);
        }
    }
}
`);
</script>
