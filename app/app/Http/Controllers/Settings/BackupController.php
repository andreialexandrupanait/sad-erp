<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\Database\DatabaseBackupService;
use App\Services\Database\DatabaseRestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    protected DatabaseBackupService $backupService;
    protected DatabaseRestoreService $restoreService;

    public function __construct(
        DatabaseBackupService $backupService,
        DatabaseRestoreService $restoreService
    ) {
        $this->backupService = $backupService;
        $this->restoreService = $restoreService;

        // Only admins can access backup functionality
        $this->middleware(function ($request, $next) {
            if (!in_array(auth()->user()->role, ['admin', 'superadmin'])) {
                abort(403, __('Only administrators can manage backups.'));
            }
            return $next($request);
        });
    }

    /**
     * Show backup management page
     */
    public function index()
    {
        $backups = $this->backupService->getExistingBackups();
        $tables = $this->backupService->getAvailableTables();

        return view('settings.backup.index', [
            'backups' => $backups,
            'tables' => $tables,
        ]);
    }

    /**
     * Create a new backup
     */
    public function export(Request $request)
    {
        $request->validate([
            'tables' => 'nullable|array',
            'tables.*' => 'string',
        ]);

        $tables = $request->input('tables');
        $result = $this->backupService->createBackup($tables);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => __('Backup created successfully.'),
                'filename' => $result['filename'],
                'download_url' => route('settings.backup.download', $result['filename']),
                'size' => $result['size'],
                'tables_count' => $result['tables_count'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('Backup creation failed.'),
        ], 500);
    }

    /**
     * Download a backup file
     */
    public function download(string $filename)
    {
        $path = 'backups/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            abort(404, __('Backup not found.'));
        }

        return Storage::disk('local')->download($path, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Import database from uploaded JSON file
     */
    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json|max:51200', // 50MB max
            'mode' => 'required|in:merge,replace',
        ]);

        $file = $request->file('backup_file');
        $mode = $request->input('mode');

        // Save uploaded file temporarily
        $tempFilename = 'temp_restore_' . time() . '.json';
        $tempPath = 'backups/' . $tempFilename;
        Storage::disk('local')->put($tempPath, file_get_contents($file->getRealPath()));

        // Perform restore
        $result = $this->restoreService->restoreFromBackup($tempFilename, $mode);

        // Clean up temp file
        Storage::disk('local')->delete($tempPath);

        if ($result['success']) {
            $message = __('Import completed successfully.');
            if (!empty($result['errors'])) {
                $message .= ' ' . __('Some tables were skipped.');
            }

            return back()
                ->with('success', $message)
                ->with('import_results', [
                    'imported' => $result['imported'],
                    'errors' => $result['errors'],
                ]);
        }

        return back()->with('error', __('Import failed: :error', ['error' => $result['error'] ?? 'Unknown error']));
    }

    /**
     * Restore from an existing backup file
     */
    public function restore(Request $request, string $filename)
    {
        $request->validate([
            'mode' => 'required|in:merge,replace',
        ]);

        $mode = $request->input('mode');

        // Validate backup file exists
        $validation = $this->backupService->validateBackupFile($filename);

        if (!$validation['exists']) {
            return back()->with('error', __('Backup not found.'));
        }

        if (!$validation['valid']) {
            return back()->with('error', __('Invalid backup file format.'));
        }

        // Perform restore
        $result = $this->restoreService->restoreFromBackup($filename, $mode);

        if ($result['success']) {
            $message = __('Restore completed successfully.');
            if (!empty($result['errors'])) {
                $message .= ' ' . __('Some tables were skipped.');
            }

            return back()
                ->with('success', $message)
                ->with('import_results', [
                    'imported' => $result['imported'],
                    'errors' => $result['errors'],
                ]);
        }

        return back()->with('error', __('Restore failed: :error', ['error' => $result['error'] ?? 'Unknown error']));
    }

    /**
     * Delete a backup file
     */
    public function destroy(string $filename)
    {
        $deleted = $this->backupService->deleteBackup($filename);

        if ($deleted) {
            return back()->with('success', __('Backup deleted successfully.'));
        }

        return back()->with('error', __('Backup not found.'));
    }
}
