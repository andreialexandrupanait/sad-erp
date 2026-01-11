<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Financial\Concerns\ManagesImports;
use App\Http\Controllers\Traits\SafeJsonResponse;
use App\Models\FinancialRevenue;
use App\Models\SmartbillImport;
use App\Jobs\ImportSmartbillInvoicesJob;
use App\Services\Financial\RevenueImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RevenueImportController extends Controller
{
    use SafeJsonResponse, ManagesImports;

    protected RevenueImportService $revenueImportService;

    public function __construct(RevenueImportService $revenueImportService)
    {
        $this->revenueImportService = $revenueImportService;
    }

    /**
     * Show revenue import form
     */
    public function showForm()
    {
        $this->authorize('import', FinancialRevenue::class);

        $recentImports = SmartbillImport::where('organization_id', auth()->user()->organization_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('financial.revenues.import', compact('recentImports'));
    }

    /**
     * Preview import before processing
     */
    public function preview(Request $request)
    {
        $this->authorize('import', FinancialRevenue::class);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xls,xlsx|max:5120',
            'update_mode' => 'nullable|boolean',
        ]);

        try {
            $file = $request->file('csv_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $updateMode = $request->boolean('update_mode', false);

            // Parse file using service
            $csvData = $this->revenueImportService->parseFile($file->getRealPath(), $extension);

            // Get preview data using service
            $previewData = $this->revenueImportService->preview($csvData, 50, $updateMode);

            return response()->json([
                'success' => true,
                'is_smartbill' => $previewData['is_smartbill'],
                'summary' => $previewData['summary'],
                'preview_rows' => $previewData['preview_rows'],
                'has_more' => $previewData['has_more'],
                'update_mode' => $previewData['update_mode'],
            ]);
        } catch (\Exception $e) {
            return $this->safeJsonError($e, 'Revenue preview');
        }
    }

    /**
     * Import revenues from uploaded file
     */
    public function import(Request $request)
    {
        $this->authorize('import', FinancialRevenue::class);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xls,xlsx|max:5120',
            'download_smartbill_pdfs' => 'nullable|boolean',
            'dry_run' => 'nullable|boolean',
            'update_mode' => 'nullable|boolean',
        ]);

        $file = $request->file('csv_file');
        $extension = strtolower($file->getClientOriginalExtension());

        // Parse file based on extension
        if (in_array($extension, ['xls', 'xlsx'])) {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $csvData = $worksheet->toArray();

            // Convert to CSV format for storage
            $csvContent = '';
            foreach ($csvData as $row) {
                $csvContent .= implode(',', array_map(function($cell) {
                    return '"' . str_replace('"', '""', $cell ?? '') . '"';
                }, $row)) . "\n";
            }
        } else {
            $csvContent = file_get_contents($file->getRealPath());
            $csvData = array_map('str_getcsv', explode("\n", $csvContent));
        }

        // Count rows (excluding empty ones and header)
        $totalRows = count(array_filter($csvData, function($row) {
            return !empty(array_filter($row));
        }));

        $downloadPdfs = $request->boolean('download_smartbill_pdfs', false);
        $dryRun = $request->boolean('dry_run', false);
        $updateMode = $request->boolean('update_mode', false);

        // For small files (< 50 rows) without PDF download, process synchronously
        if ($totalRows < 50 && !$downloadPdfs) {
            return $this->importSynchronously($csvData, $downloadPdfs, $dryRun, $updateMode);
        }

        // For larger files or when downloading PDFs, use background job
        $organization = auth()->user()->organization;
        $userId = auth()->id();

        // Store file temporarily
        $tempFileName = 'imports/smartbill_' . uniqid() . '.csv';
        Storage::disk('local')->put($tempFileName, $csvContent);

        // Create import record
        $import = SmartbillImport::create([
            'organization_id' => $organization->id,
            'user_id' => $userId,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $tempFileName,
            'status' => 'pending',
            'options' => [
                'download_pdfs' => $downloadPdfs,
                'dry_run' => $dryRun,
                'update_mode' => $updateMode,
            ],
            'total_rows' => $totalRows - 1, // Subtract header row
            'processed_rows' => 0,
        ]);

        // Dispatch background job
        ImportSmartbillInvoicesJob::dispatch($import->id, $organization->id, $userId);

        return redirect()
            ->route('financial.revenues.import')
            ->with('success', "Import started! Processing {$totalRows} rows in the background. You can track progress below.");
    }

    /**
     * Process small imports synchronously
     */
    protected function importSynchronously(array $csvData, bool $downloadPdfs, bool $dryRun, bool $updateMode = false)
    {
        // Get Smartbill settings if PDF download is requested
        $smartbillSettings = null;
        if ($downloadPdfs) {
            $organization = auth()->user()->organization;
            $smartbillSettings = $organization->settings['smartbill'] ?? null;
        }

        // Use the service for import
        $stats = $this->revenueImportService->import(
            $csvData,
            auth()->user()->organization_id,
            auth()->id(),
            $downloadPdfs,
            $dryRun,
            $smartbillSettings,
            null, // progressCallback
            $updateMode
        );

        $message = "Import completed: {$stats['imported']} revenues imported";
        if (($stats['updated'] ?? 0) > 0) {
            $message .= ", {$stats['updated']} EUR records updated with RON values";
        }
        $message .= ", {$stats['skipped']} skipped";
        if ($stats['pdfs_downloaded'] > 0) {
            $message .= ", {$stats['pdfs_downloaded']} PDFs downloaded from Smartbill";
        }

        if ($dryRun) {
            $dryRunMsg = "DRY RUN: Would import {$stats['imported']} revenues";
            if (($stats['updated'] ?? 0) > 0) {
                $dryRunMsg .= ", would update {$stats['updated']} EUR records";
            }
            $dryRunMsg .= ", {$stats['duplicates']} duplicates would be skipped";
            $message = $dryRunMsg;
        }

        $flashData = [
            'success' => $message,
            'import_errors' => $stats['errors'],
        ];

        if (!empty($stats['duplicates_found'])) {
            $flashData['duplicates_found'] = $stats['duplicates_found'];
        }

        if (!empty($stats['updated_revenues'])) {
            $flashData['updated_revenues'] = $stats['updated_revenues'];
        }

        return redirect()
            ->route('financial.revenues.index')
            ->with($flashData);
    }

    /**
     * Download CSV template for revenue import
     */
    public function downloadTemplate()
    {
        $filename = 'revenue_import_template_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'document_name',
                'amount',
                'currency',
                'occurred_at',
                'client_name',
                'note'
            ]);

            // Example row
            fputcsv($file, [
                'Factura #2025001',
                '1500.00',
                'RON',
                date('Y-m-d'),
                'Example Client SRL',
                'Monthly retainer fee'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export revenues to CSV
     */
    public function export(Request $request)
    {
        $this->authorize('export', FinancialRevenue::class);

        $year = $request->get('year', now()->year);
        $month = $request->get('month');
        $currency = $request->get('currency');
        $clientId = $request->get('client_id');

        $revenues = FinancialRevenue::with('client')
            ->where('organization_id', auth()->user()->organization_id)
            ->forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->orderBy('occurred_at')
            ->get();

        $filename = 'revenues_export_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($revenues) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'document_name',
                'amount',
                'currency',
                'occurred_at',
                'client_name',
                'client_tax_id',
                'note',
                'year',
                'month'
            ]);

            foreach ($revenues as $revenue) {
                fputcsv($file, [
                    $revenue->document_name,
                    $revenue->amount,
                    $revenue->currency,
                    $revenue->occurred_at->format('Y-m-d'),
                    $revenue->client?->name ?? '',
                    $revenue->client?->tax_id ?? '',
                    $revenue->note ?? '',
                    $revenue->year,
                    $revenue->month,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
