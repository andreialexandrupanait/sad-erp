<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\SmartbillService;
use App\Services\Financial\RevenueImportService;
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
        $smartbillSettings = $organization->getSmartbillSettings();

        // Check if credentials are configured
        $hasCredentials = !empty($smartbillSettings['username']) &&
                          !empty($smartbillSettings['token']) &&
                          !empty($smartbillSettings['cif']);

        // Mask the token for display (show only last 4 chars)
        if (!empty($smartbillSettings['token'])) {
            $smartbillSettings['token_masked'] = '••••••••' . substr($smartbillSettings['token'], -4);
            unset($smartbillSettings['token']); // Don't expose full token to view
        }

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
        $organization->setSmartbillSettings($validated);
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
            $smartbillSettings = $organization->getSmartbillSettings();

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
        $smartbillSettings = $organization->getSmartbillSettings();

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

            // Process the import synchronously
            $this->processImportData($importId, $data);

            return response()->json([
                'success' => true,
                'message' => 'Import processing started'
            ]);

        } catch (\Exception $e) {
            Log::error('Smartbill import processing failed', [
                'import_id' => $importId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
     * Get import progress
     */
    public function getProgress($importId)
    {
        $progress = Cache::get("import:{$importId}:progress");

        if (!$progress) {
            return response()->json(['error' => 'Import not found'], 404);
        }

        return response()->json($progress);
    }

    /**
     * Process import data using RevenueImportService
     */
    private function processImportData($importId, $data)
    {
        $csvData = $data['csv_data'];
        $downloadPdfs = $data['download_pdfs'];
        $userId = $data['user_id'];
        $organizationId = $data['organization_id'];

        // Get smartbill settings for PDF downloads
        $organization = Organization::find($organizationId);
        $smartbillSettings = $organization->getSmartbillSettings() ?: null;

        // Use the RevenueImportService (resolved from container with dependencies)
        $importService = app(RevenueImportService::class);

        // Find header row to get total count
        [$headerRowIndex, $header] = $importService->findHeaderRow($csvData);
        $dataRows = array_slice($csvData, $headerRowIndex + 1);
        $dataRows = array_filter($dataRows, fn($row) => !empty(array_filter($row)));
        $total = count($dataRows);

        $this->updateProgress($importId, [
            'total' => $total,
            'status' => 'processing',
            'message' => "Found {$total} invoices to process..."
        ]);

        try {
            // Run the import with progress callback
            $stats = $importService->import(
                $csvData,
                $organizationId,
                $userId,
                $downloadPdfs,
                false, // not a dry run
                $smartbillSettings,
                function($processed, $total, $stats) use ($importId) {
                    $this->updateProgress($importId, [
                        'total' => $total,
                        'processed' => $processed,
                        'created' => $stats['imported'],
                        'skipped' => $stats['skipped'],
                        'duplicates' => $stats['duplicates'],
                        'errors' => count($stats['errors']),
                        'pdfs_downloaded' => $stats['pdfs_downloaded'],
                        'status' => 'processing',
                        'message' => sprintf('Processing %d of %d invoices...', $processed, $total)
                    ]);
                    
                    // Add small delay every 10 rows to prevent timeout
                    if ($processed % 10 === 0) {
                    }
                }
            );

            // Mark as completed
            $this->updateProgress($importId, [
                'total' => $total,
                'processed' => $total,
                'created' => $stats['imported'],
                'skipped' => $stats['skipped'],
                'duplicates' => $stats['duplicates'],
                'errors' => count($stats['errors']),
                'pdfs_downloaded' => $stats['pdfs_downloaded'],
                'clients_created' => $stats['clients_created'],
                'status' => 'completed',
                'message' => sprintf(
                    'Import completed! %d created, %d duplicates, %d skipped.',
                    $stats['imported'],
                    $stats['duplicates'],
                    $stats['skipped']
                ),
                'errors_list' => $stats['errors'],
                'duplicates_found' => $stats['duplicates_found'] ?? []
            ]);

        } catch (\Exception $e) {
            Log::error('Import failed', [
                'import_id' => $importId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->updateProgress($importId, [
                'status' => 'failed',
                'message' => 'Import failed: ' . $e->getMessage(),
                'errors_list' => [$e->getMessage()]
            ]);

            throw $e;
        }

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
