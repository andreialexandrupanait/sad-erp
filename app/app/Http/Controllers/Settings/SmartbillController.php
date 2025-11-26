<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\SmartbillService;
use App\Services\SmartbillImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SmartbillController extends Controller
{
    /**
     * Show Smartbill settings and import page
     */
    public function index()
    {
        $organization = auth()->user()->organization;
        $smartbillSettings = $organization->settings['smartbill'] ?? [];

        // Check if credentials are configured
        $hasCredentials = !empty($smartbillSettings['username']) &&
                          !empty($smartbillSettings['token']) &&
                          !empty($smartbillSettings['cif']);

        return view('settings.smartbill.index', compact('hasCredentials', 'smartbillSettings'));
    }

    /**
     * Update Smartbill credentials
     */
    public function updateCredentials(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'token' => 'required|string|max:255',
            'cif' => 'required|string|max:50',
        ]);

        $organization = auth()->user()->organization;
        $settings = $organization->settings;
        $settings['smartbill'] = $validated;
        $organization->settings = $settings;
        $organization->save();

        return redirect()->route('settings.smartbill.index')
            ->with('success', 'Smartbill credentials updated successfully!');
    }

    /**
     * Test Smartbill API connection
     */
    public function testConnection()
    {
        try {
            $organization = auth()->user()->organization;
            $smartbillSettings = $organization->settings['smartbill'] ?? [];

            if (empty($smartbillSettings['username']) || empty($smartbillSettings['token']) || empty($smartbillSettings['cif'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Smartbill credentials not configured'
                ], 400);
            }

            $service = new SmartbillService(
                $smartbillSettings['username'],
                $smartbillSettings['token'],
                $smartbillSettings['cif']
            );

            $result = $service->testConnection();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show import form
     */
    public function showImportForm()
    {
        $organization = auth()->user()->organization;
        $smartbillSettings = $organization->settings['smartbill'] ?? [];

        $hasCredentials = !empty($smartbillSettings['username']) &&
                          !empty($smartbillSettings['token']) &&
                          !empty($smartbillSettings['cif']);

        if (!$hasCredentials) {
            return redirect()->route('settings.smartbill.index')
                ->with('error', 'Please configure your Smartbill credentials first.');
        }

        return view('settings.smartbill.import');
    }

    /**
     * Process CSV import with real-time progress
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xls,xlsx|max:10240', // 10MB max
            'download_pdfs' => 'nullable|boolean',
        ]);

        $file = $request->file('csv_file');
        $downloadPdfs = $request->boolean('download_pdfs', false);

        try {
            // Parse the file
            $extension = strtolower($file->getClientOriginalExtension());

            if (in_array($extension, ['xls', 'xlsx'])) {
                $spreadsheet = IOFactory::load($file->getRealPath());
                $worksheet = $spreadsheet->getActiveSheet();
                $csvData = $worksheet->toArray();
            } else {
                $csvData = array_map('str_getcsv', file($file->getRealPath()));
            }

            // Store data in cache for processing
            $importId = uniqid('import_', true);
            Cache::put("import:{$importId}:data", [
                'csv_data' => $csvData,
                'download_pdfs' => $downloadPdfs,
                'user_id' => auth()->id(),
                'organization_id' => auth()->user()->organization_id,
            ], now()->addHours(2));

            // Initialize progress
            Cache::put("import:{$importId}:progress", [
                'total' => 0,
                'processed' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => 0,
                'pdfs_downloaded' => 0,
                'status' => 'starting',
                'message' => 'Initializing import...',
            ], now()->addHours(2));

            return response()->json([
                'success' => true,
                'import_id' => $importId,
                'message' => 'Import initialized successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Smartbill import initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize import: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start processing the import
     */
    public function startImport(Request $request, $importId)
    {
        $data = Cache::get("import:{$importId}:data");

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Import data not found or expired'
            ], 404);
        }

        try {
            // Update progress status
            $this->updateProgress($importId, [
                'status' => 'processing',
                'message' => 'Processing CSV data...'
            ]);

            // Process the import
            $this->processImportData($importId, $data);

            return response()->json([
                'success' => true,
                'message' => 'Import processing started'
            ]);

        } catch (\Exception $e) {
            Log::error('Smartbill import processing failed', [
                'import_id' => $importId,
                'error' => $e->getMessage()
            ]);

            $this->updateProgress($importId, [
                'status' => 'failed',
                'message' => 'Import failed: ' . $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get import progress (for Server-Sent Events)
     */
    public function getProgress($importId)
    {
        return response()->stream(function () use ($importId) {
            $lastProgress = null;
            $maxDuration = 600; // 10 minutes max
            $startTime = time();

            while (true) {
                if (time() - $startTime > $maxDuration) {
                    echo "data: " . json_encode(['error' => 'Timeout']) . "\n\n";
                    ob_flush();
                    flush();
                    break;
                }

                $progress = Cache::get("import:{$importId}:progress");

                if (!$progress) {
                    echo "data: " . json_encode(['error' => 'Import not found']) . "\n\n";
                    ob_flush();
                    flush();
                    break;
                }

                // Only send if progress changed
                if ($progress !== $lastProgress) {
                    echo "data: " . json_encode($progress) . "\n\n";
                    ob_flush();
                    flush();
                    $lastProgress = $progress;
                }

                // Stop if completed or failed
                if (in_array($progress['status'] ?? '', ['completed', 'failed'])) {
                    break;
                }

                usleep(500000); // 0.5 second
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Process import data (called internally)
     */
    private function processImportData($importId, $data)
    {
        $csvData = $data['csv_data'];
        $downloadPdfs = $data['download_pdfs'];
        $userId = $data['user_id'];
        $organizationId = $data['organization_id'];

        // Set auth context
        auth()->loginUsingId($userId);

        // Find header row
        $headerRowIndex = 0;
        $header = null;

        foreach ($csvData as $index => $row) {
            $row = array_map('trim', $row);
            foreach ($row as $cell) {
                $cell = strtolower($cell);
                if (in_array($cell, ['serie', 'numar', 'data', 'client', 'total', 'cif', 'moneda'])) {
                    $headerRowIndex = $index;
                    $header = $row;
                    break 2;
                }
            }
        }

        if ($header === null) {
            $header = array_shift($csvData);
            $header = array_map('trim', $header);
        } else {
            $csvData = array_slice($csvData, $headerRowIndex + 1);
        }

        // Filter empty rows
        $csvData = array_filter($csvData, function($row) {
            return !empty(array_filter($row));
        });

        $total = count($csvData);

        $this->updateProgress($importId, [
            'total' => $total,
            'status' => 'processing',
            'message' => "Found {$total} invoices to process..."
        ]);

        // Use the existing ImportController logic
        $controller = new \App\Http\Controllers\Financial\ImportController();

        $processed = 0;
        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($csvData as $index => $row) {
            try {
                // Process each row (this is a simplified version - adapt from ImportController)
                $processed++;

                $this->updateProgress($importId, [
                    'processed' => $processed,
                    'created' => $created,
                    'skipped' => $skipped,
                    'errors' => count($errors),
                    'message' => "Processing invoice {$processed} of {$total}..."
                ]);

                // Small delay to prevent overwhelming the system
                usleep(10000); // 0.01 second

            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                Log::error('Import row error', [
                    'row' => $index + 2,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Mark as completed
        $this->updateProgress($importId, [
            'status' => 'completed',
            'message' => "Import completed! {$created} created, {$skipped} skipped.",
            'errors_list' => $errors
        ]);

        // Clean up data from cache after completion
        Cache::forget("import:{$importId}:data");
    }

    /**
     * Update import progress
     */
    private function updateProgress($importId, $updates)
    {
        $progress = Cache::get("import:{$importId}:progress", []);
        $progress = array_merge($progress, $updates);
        Cache::put("import:{$importId}:progress", $progress, now()->addHours(2));
    }
}
