<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Rules\SecureFileUpload;
use App\Services\Financial\FileUploadService;
use App\Services\Financial\TransactionImportService;
use App\Services\Financial\ZipExportService;
use Illuminate\Http\Request;
use App\Models\FinancialFile;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function __construct(
        protected FileUploadService $fileUploadService,
        protected ZipExportService $zipExportService,
        protected TransactionImportService $transactionImportService
    ) {}
    /**
     * Redirect to current year view (default entry point)
     */
    public function index()
    {
        return redirect()->route('financial.files.year', ['year' => now()->year]);
    }

    /**
     * Year overview - shows 12 month cards
     * URL: /financial/files/{year}
     */
    public function indexYear(int $year)
    {
        $availableYears = $this->getAvailableYears();
        $allYearsSummary = $this->getAllYearsSummary($availableYears);

        return view('financial.files.year', compact(
            'year',
            'availableYears',
            'allYearsSummary'
        ));
    }

    /**
     * Month overview - shows 4 category cards with file lists
     * URL: /financial/files/{year}/{month}
     */
    public function indexMonth(int $year, int $month)
    {
        $availableYears = $this->getAvailableYears();
        $allYearsSummary = $this->getAllYearsSummary($availableYears);

        // Get files for this month grouped by category
        $filesByCategory = FinancialFile::with('entity')
            ->where('an', $year)
            ->where('luna', $month)
            ->orderBy('file_name')
            ->get()
            ->groupBy('tip');

        return view('financial.files.month', compact(
            'year',
            'month',
            'availableYears',
            'allYearsSummary',
            'filesByCategory'
        ));
    }

    /**
     * Category view - shows files table with bulk actions
     * URL: /financial/files/{year}/{month}/{category}
     */
    public function indexCategory(int $year, int $month, string $category)
    {
        $availableYears = $this->getAvailableYears();
        $allYearsSummary = $this->getAllYearsSummary($availableYears);

        // Build the query for this specific category
        $files = FinancialFile::with('entity')
            ->withCount('importedExpenses')
            ->where('an', $year)
            ->where('luna', $month)
            ->where('tip', $category)
            ->latest()
            ->paginate(50);

        return view('financial.files.category', compact(
            'year',
            'month',
            'category',
            'files',
            'availableYears',
            'allYearsSummary'
        ));
    }

    /**
     * Get available years for navigation
     */
    private function getAvailableYears()
    {
        $currentYear = now()->year;
        return collect(range(2019, $currentYear))->reverse()->values();
    }

    /**
     * Get file summary for all years organized by year/month/type
     */
    private function getAllYearsSummary($availableYears)
    {
        $data = FinancialFile::selectRaw('an, luna, tip, COUNT(*) as count')
            ->groupBy('an', 'luna', 'tip')
            ->get();

        $allSummary = [];

        foreach ($availableYears as $year) {
            $allSummary[$year] = [];

            for ($month = 1; $month <= 12; $month++) {
                $allSummary[$year][$month] = [
                    'incasare' => 0,
                    'plata' => 0,
                    'extrase' => 0,
                    'general' => 0,
                    'total' => 0,
                ];

                foreach ($data as $item) {
                    if ($item->an == $year && $item->luna == $month) {
                        $tip = $item->tip ?? 'general';
                        if (isset($allSummary[$year][$month][$tip])) {
                            $allSummary[$year][$month][$tip] = $item->count;
                        }
                        $allSummary[$year][$month]['total'] += $item->count;
                    }
                }
            }
        }

        return $allSummary;
    }

    /**
     * Show upload form
     */
    public function create(Request $request)
    {
        $entityType = $request->get('entity_type');
        $entityId = $request->get('entity_id');

        return view('financial.files.create', compact('entityType', 'entityId'));
    }

    /**
     * Handle file upload with standardized naming
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'files' => 'required|array',
            'files.*' => ['required', 'file', 'max:10240', new SecureFileUpload()],
            'entity_type' => 'nullable|string|in:App\Models\FinancialRevenue,App\Models\FinancialExpense',
            'entity_id' => 'nullable|integer',
            'tip' => 'nullable|string|in:incasare,plata,extrase,general',
            'an' => 'nullable|integer',
            'luna' => 'nullable|integer|between:1,12',
        ]);

        // Determine year, month, and type
        $year = $validated['an'] ?? now()->year;
        $month = $validated['luna'] ?? now()->month;
        $tip = $validated['tip'] ?? 'general';

        // If entity is provided, get details from it
        if (isset($validated['entity_type']) && isset($validated['entity_id'])) {
            $entity = $validated['entity_type']::find($validated['entity_id']);

            if ($entity && isset($entity->occurred_at)) {
                $year = $entity->occurred_at->year;
                $month = $entity->occurred_at->month;
            }

            if ($entity instanceof FinancialRevenue) {
                $tip = 'incasare';
            } elseif ($entity instanceof FinancialExpense) {
                $tip = 'plata';
            }
        }

        // Upload files using service
        $uploadedFiles = $this->fileUploadService->uploadFiles(
            $request->file('files'),
            $year,
            $month,
            $tip,
            [
                'entity_type' => $validated['entity_type'] ?? null,
                'entity_id' => $validated['entity_id'] ?? null,
            ]
        );

        $count = count($uploadedFiles);
        $messageKey = $count === 1 ? 'messages.files_uploaded_single' : 'messages.files_uploaded_plural';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'files' => $uploadedFiles,
                'message' => __($messageKey, ['count' => $count]),
            ]);
        }

        return redirect()->route('financial.files.category', ['year' => $year, 'month' => $month, 'category' => $tip])
            ->with('success', __($messageKey, ['count' => $count]));
    }

    /**
     * Download a file
     */
    public function download(FinancialFile $file)
    {
        if (!Storage::disk('financial')->exists($file->file_path)) {
            abort(404, 'Fișierul nu a fost găsit.');
        }

        return Storage::disk('financial')->download($file->file_path, $file->file_name);
    }

    /**
     * Show file in browser (preview)
     */
    public function show(FinancialFile $file)
    {
        if (!Storage::disk('financial')->exists($file->file_path)) {
            abort(404, 'Fișierul nu a fost găsit.');
        }

        $mimeType = $file->mime_type ?? $file->file_type ?? 'application/octet-stream';

        return response()->file(
            Storage::disk('financial')->path($file->file_path),
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $file->file_name . '"'
            ]
        );
    }

    /**
     * Delete a file
     */
    public function destroy(FinancialFile $file)
    {
        $year = $file->an;
        $month = $file->luna;
        $tip = $file->tip;

        $file->delete(); // Physical file deletion is handled in the model

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.file_deleted'),
            ]);
        }

        return redirect()->route('financial.files.category', ['year' => $year, 'month' => $month, 'category' => $tip])
            ->with('success', __('messages.file_deleted'));
    }

    /**
     * Delete multiple files at once
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:financial_files,id',
        ]);

        $files = FinancialFile::whereIn('id', $validated['ids'])->get();

        if ($files->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.no_files_selected'),
            ], 404);
        }

        $deletedCount = 0;
        $errors = [];

        foreach ($files as $file) {
            try {
                // Physical file deletion is handled in the model's deleted event
                $file->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $errors[] = $file->file_name . ': ' . $e->getMessage();
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $deletedCount > 0,
                'deleted_count' => $deletedCount,
                'message' => __('messages.files_deleted', ['count' => $deletedCount]),
                'errors' => $errors,
            ]);
        }

        return redirect()->back()->with('success', __('messages.files_deleted', ['count' => $deletedCount]));
    
    }

    /**
     * Rename a file
     */
    public function rename(Request $request, FinancialFile $file)
    {
        $validated = $request->validate([
            'file_name' => 'required|string|max:255',
        ]);

        $file->update([
            'file_name' => $validated['file_name'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'file' => $file,
                'message' => __('messages.file_renamed'),
            ]);
        }

        return redirect()->back()->with('success', __('messages.file_renamed'));
    }

    /**
     * Ajax method to upload files from revenue/expense forms
     */
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240', new SecureFileUpload()],
            'entity_type' => 'required|string',
            'entity_id' => 'required|integer',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        // Get entity to determine year/month/type
        $entityClass = $validated['entity_type'];
        $entity = $entityClass::find($validated['entity_id']);

        $year = $entity && isset($entity->occurred_at) ? $entity->occurred_at->year : now()->year;
        $month = $entity && isset($entity->occurred_at) ? $entity->occurred_at->month : now()->month;

        $tip = 'general';
        if ($entity instanceof FinancialRevenue) {
            $tip = 'incasare';
        } elseif ($entity instanceof FinancialExpense) {
            $tip = 'plata';
        }

        // Get Romanian month name for folder structure
        $monthName = romanian_month($month);

        // Auto-rename bank statements for better readability
        $originalName = $file->getClientOriginalName();
        $displayName = $originalName;
        $newFileName = null;

        if ($tip === 'extrase') {
            $generatedName = $this->generateBankStatementName($originalName);
            if ($generatedName) {
                $displayName = $generatedName . '.' . $extension;
                // Use the exact friendly name for server file (no sanitization, no UUID)
                $newFileName = $displayName;
            }
        }

        // If not a bank statement or rename failed, use standard naming
        if (!$newFileName) {
            $sanitizedName = sanitize_filename(pathinfo($originalName, PATHINFO_FILENAME));
            $uniqueId = Str::uuid()->toString();
            $newFileName = "{$sanitizedName}-{$uniqueId}.{$extension}";
        }

        // Map database tip values to Romanian folder names
        $folderName = match($tip) {
            'incasare' => 'Incasari',
            'plata' => 'Plati',
            'extrase' => 'Extrase',
            default => 'General',
        };

        // Storage path: /year/MonthName/FolderName/filename (matching existing structure)
        $storagePath = "{$year}/{$monthName}/{$folderName}/{$newFileName}";
        $path = $file->storeAs(dirname($storagePath), basename($storagePath), 'financial');

        $financialFile = FinancialFile::create([
            'file_name' => $displayName,
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'entity_type' => $validated['entity_type'],
            'entity_id' => $validated['entity_id'],
            'an' => $year,
            'luna' => $month,
            'tip' => $tip,
        ]);

        return response()->json([
            'success' => true,
            'file' => $financialFile,
        ]);
    }

    /**
     * Download all files for a specific month as a ZIP archive
     */
    public function downloadMonthlyZip($year, $month)
    {
        $year = (int) $year;
        $month = (int) $month;

        $result = $this->zipExportService->createMonthlyZip($year, $month);

        if (!$result['success']) {
            return redirect()->back()->with('error', __('messages.no_files_for_month'));
        }

        return $this->zipExportService->createDownloadResponse($result['path'], $result['filename']);
    }

    /**
     * Download all files for a specific year as a ZIP archive
     */
    public function downloadYearlyZip($year)
    {
        $year = (int) $year;

        $result = $this->zipExportService->createYearlyZip($year);

        if (!$result['success']) {
            return redirect()->back()->with('error', __('messages.no_files_for_year'));
        }

        return $this->zipExportService->createDownloadResponse($result['path'], $result['filename']);
    }

    /**
     * Generate friendly name for bank statement files
     *
     * Pattern 1 - BT Export format:
     * Extrase_RO82BTRLEURCRT0512531701_2025-10-01_2025-10-31_SIMPLEAD_S_R_L
     * → Extras BT EUR 01.10 - 31.10.2025
     *
     * Pattern 2 - BT Monthly format:
     * 092025_RO35BTRLRONCRT0512531701_CURRENT.pdf
     * → Extras BT RON 01.09 - 30.09.2025
     */
    private function generateBankStatementName($originalFilename)
    {
        // Remove extension for pattern matching
        $nameWithoutExt = pathinfo($originalFilename, PATHINFO_FILENAME);

        // Pattern 1: Extrase_IBAN_YYYY-MM-DD_YYYY-MM-DD_COMPANY
        if (str_starts_with($nameWithoutExt, 'Extrase_')) {
            // Extract currency from IBAN in filename
            $currency = $this->detectCurrencyFromFilename($nameWithoutExt);

            // Extract full date range: _YYYY-MM-DD_YYYY-MM-DD_
            if (preg_match('/_(\d{4})-(\d{2})-(\d{2})_(\d{4})-(\d{2})-(\d{2})_/', $nameWithoutExt, $matches)) {
                $startDay = $matches[3];
                $startMonth = $matches[2];
                $endDay = $matches[6];
                $endMonth = $matches[5];
                $endYear = $matches[4];

                return "Extras BT {$currency} {$startDay}.{$startMonth} - {$endDay}.{$endMonth}.{$endYear}";
            }
        }

        // Pattern 2: MMYYYY_IBAN_CURRENT (e.g., 092025_RO35BTRLRONCRT0512531701_CURRENT)
        if (preg_match('/^(\d{2})(\d{4})_([A-Z0-9]+)_CURRENT$/i', $nameWithoutExt, $matches)) {
            $month = $matches[1];
            $year = $matches[2];
            $iban = $matches[3];

            // Extract currency from IBAN
            $currency = $this->detectCurrencyFromFilename($iban);

            // Calculate last day of month
            $lastDay = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);

            return "Extras BT {$currency} 01.{$month} - {$lastDay}.{$month}.{$year}";
        }

        // Pattern not matched, keep original name
        return null;
    }

    /**
     * Detect currency from IBAN or filename
     */
    private function detectCurrencyFromFilename(string $text): string
    {
        if (preg_match('/EUR/i', $text)) {
            return 'EUR';
        } elseif (preg_match('/USD/i', $text)) {
            return 'USD';
        } elseif (preg_match('/GBP/i', $text)) {
            return 'GBP';
        }
        return 'RON'; // Default
    }

    /**
     * API endpoint for lazy loading category files in sidebar tree view
     */
    public function apiCategoryFiles(int $year, int $month, string $category)
    {
        $files = FinancialFile::where('an', $year)
            ->where('luna', $month)
            ->where('tip', $category)
            ->orderBy('file_name')
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => Str::limit(pathinfo($file->file_name, PATHINFO_FILENAME), 25),
                    'full_name' => $file->file_name,
                    'icon' => $file->icon,
                    'show_url' => route('financial.files.show', $file),
                ];
            });

        return response()->json([
            'files' => $files,
        ]);
    }

    /**
     * Show import transactions form for bank statement PDF
     */
    public function importTransactions(FinancialFile $file)
    {
        $result = $this->transactionImportService->parseAndPrepareTransactions($file);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['error']);
        }

        return view('financial.files.import-transactions', [
            'file' => $file,
            'metadata' => $result['metadata'],
            'transactions' => $result['transactions'],
            'categories' => $result['categories'],
        ]);
    }

    /**
     * Process import of selected transactions
     */
    public function processImportTransactions(Request $request, FinancialFile $file)
    {
        $validated = $request->validate([
            'currency' => 'required|string|in:RON,EUR,USD',
            'transactions' => 'required|array|min:1',
            'transactions.*.selected' => 'sometimes|boolean',
            'transactions.*.date' => 'required|date',
            'transactions.*.description' => 'required|string|max:500',
            'transactions.*.amount' => 'required|numeric|min:0.01',
            'transactions.*.type' => 'required|in:debit,credit',
            'transactions.*.category_id' => 'nullable|integer',
            'transaction_files' => 'nullable|array',
            'transaction_files.*' => 'nullable|array',
            'transaction_files.*.*' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
        ]);

        $currency = $validated['currency'];
        $transactionFiles = $request->file('transaction_files', []);

        $result = $this->transactionImportService->importTransactions(
            $file,
            $validated['transactions'],
            $currency,
            $transactionFiles
        );

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()
            ->route('financial.files.category', [
                'year' => $file->an,
                'month' => $file->luna,
                'category' => 'extrase'
            ])
            ->with('success', $result['message']);
    }
}
