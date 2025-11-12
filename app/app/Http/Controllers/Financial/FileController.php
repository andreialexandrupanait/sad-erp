<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialFile;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Display the file management interface with hierarchical organization
     */
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');
        $tip = $request->get('tip'); // incasare, plata, extrase, general

        // Get available years for the filter
        $availableYears = FinancialFile::selectRaw('DISTINCT an as year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Build the query
        $filesQuery = FinancialFile::with('entity')
            ->where('an', $year);

        if ($month) {
            $filesQuery->where('luna', $month);
        }

        if ($tip) {
            $filesQuery->where('tip', $tip);
        }

        $files = $filesQuery->latest()->paginate(50);

        // Get summary for tree view
        $summary = $this->getFileSummary($year);

        return view('financial.files.index', compact(
            'files',
            'year',
            'month',
            'tip',
            'availableYears',
            'summary'
        ));
    }

    /**
     * Get file summary organized by year/month/type
     */
    private function getFileSummary($year)
    {
        $data = FinancialFile::where('an', $year)
            ->selectRaw('luna, tip, COUNT(*) as count')
            ->groupBy('luna', 'tip')
            ->get();

        $summary = [];

        for ($month = 1; $month <= 12; $month++) {
            $summary[$month] = [
                'incasare' => 0,
                'plata' => 0,
                'extrase' => 0,
                'total' => 0,
            ];

            foreach ($data as $item) {
                if ($item->luna == $month) {
                    $tip = $item->tip ?? 'general';
                    if (isset($summary[$month][$tip])) {
                        $summary[$month][$tip] = $item->count;
                    }
                    $summary[$month]['total'] += $item->count;
                }
            }
        }

        return $summary;
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
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip,rar',
            'entity_type' => 'nullable|string|in:App\Models\FinancialRevenue,App\Models\FinancialExpense',
            'entity_id' => 'nullable|integer',
            'tip' => 'nullable|string|in:incasare,plata,extrase,general',
            'an' => 'nullable|integer',
            'luna' => 'nullable|integer|between:1,12',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();

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

        // Generate standardized file name
        $sanitizedName = $this->sanitizeFileName(pathinfo($originalName, PATHINFO_FILENAME));
        $uniqueId = Str::uuid()->toString();
        $newFileName = "{$sanitizedName}-{$uniqueId}.{$extension}";

        // Storage path: /year/month/type/filename
        $storagePath = "{$year}/{$month}/{$tip}/{$newFileName}";

        // Store file
        $path = $file->storeAs('financial_files/' . dirname($storagePath), basename($storagePath), 'local');

        // Create database record
        $financialFile = FinancialFile::create([
            'file_name' => $originalName,
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'entity_type' => $validated['entity_type'] ?? null,
            'entity_id' => $validated['entity_id'] ?? null,
            'an' => $year,
            'luna' => $month,
            'tip' => $tip,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'file' => $financialFile,
                'message' => 'Fișier încărcat cu succes',
            ]);
        }

        return redirect()->route('financial.files.index', ['year' => $year, 'month' => $month])
            ->with('success', 'Fișier încărcat cu succes');
    }

    /**
     * Download a file
     */
    public function download(FinancialFile $file)
    {
        if (!Storage::exists($file->file_path)) {
            abort(404, 'Fișierul nu a fost găsit.');
        }

        return Storage::download($file->file_path, $file->file_name);
    }

    /**
     * Show file in browser (preview)
     */
    public function show(FinancialFile $file)
    {
        if (!Storage::exists($file->file_path)) {
            abort(404, 'Fișierul nu a fost găsit.');
        }

        $mimeType = $file->mime_type ?? $file->file_type ?? 'application/octet-stream';

        return response()->file(
            Storage::path($file->file_path),
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

        $file->delete(); // Physical file deletion is handled in the model

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fișier șters cu succes',
            ]);
        }

        return redirect()->route('financial.files.index', ['year' => $year, 'month' => $month])
            ->with('success', 'Fișier șters cu succes');
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
                'message' => 'Fișier redenumit cu succes',
            ]);
        }

        return redirect()->back()->with('success', 'Fișier redenumit cu succes');
    }

    /**
     * Ajax method to upload files from revenue/expense forms
     */
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip,rar',
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

        // Generate file name
        $sanitizedName = $this->sanitizeFileName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $uniqueId = Str::uuid()->toString();
        $newFileName = "{$sanitizedName}-{$uniqueId}.{$extension}";

        $storagePath = "{$year}/{$month}/{$tip}/{$newFileName}";
        $path = $file->storeAs('financial_files/' . dirname($storagePath), basename($storagePath), 'local');

        $financialFile = FinancialFile::create([
            'file_name' => $file->getClientOriginalName(),
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
        // Get all files for the specified month
        $files = FinancialFile::where('an', $year)
            ->where('luna', $month)
            ->get();

        if ($files->isEmpty()) {
            return redirect()->back()->with('error', 'Nu există fișiere pentru luna selectată.');
        }

        // Create a temporary file for the ZIP
        $zipFileName = "financiar_{$year}_{$month}_" . now()->format('YmdHis') . ".zip";
        $zipPath = storage_path("app/temp/{$zipFileName}");

        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Create ZIP archive
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Nu s-a putut crea arhiva ZIP.');
        }

        // Add files to ZIP, organized by type
        foreach ($files as $file) {
            if (Storage::exists($file->file_path)) {
                $filePath = Storage::path($file->file_path);
                $tip = $file->tip ?? 'general';

                // Add file to ZIP in folder structure: type/filename
                $zip->addFile($filePath, "{$tip}/{$file->file_name}");
            }
        }

        $zip->close();

        // Download and delete the temporary ZIP file
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * Sanitize file name for storage
     */
    private function sanitizeFileName($name)
    {
        // Remove special characters, keep alphanumeric, dash, underscore
        $name = preg_replace('/[^A-Za-z0-9\-_]/', '-', $name);
        // Remove multiple dashes
        $name = preg_replace('/-+/', '-', $name);
        // Trim dashes from start and end
        $name = trim($name, '-');
        // Limit length
        $name = Str::limit($name, 100, '');

        return $name ?: 'file';
    }
}
