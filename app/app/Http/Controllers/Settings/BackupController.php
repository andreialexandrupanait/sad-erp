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
     * 
     * SECURITY FIX: Added path traversal protection
     */
    public function download(string $filename)
    {
        // SECURITY: Prevent path traversal attacks
        // Only allow alphanumeric, hyphens, underscores, and dots
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
            \Log::warning('Backup download attempt with invalid filename', [
                'filename' => $filename,
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
            ]);
            abort(403, __('Invalid filename format.'));
        }

        // Use basename() to strip any remaining path components
        $safeFilename = basename($filename);

        // Construct the full path
        $path = 'backups/' . $safeFilename;

        // Check if file exists
        if (!Storage::disk('local')->exists($path)) {
            abort(404, __('Backup not found.'));
        }

        // SECURITY: Verify the real path is within the backups directory
        // This prevents symlink attacks
        $realPath = Storage::disk('local')->path($path);
        $backupsPath = Storage::disk('local')->path('backups');

        // Ensure both paths exist and are resolved
        if (!file_exists($realPath)) {
            abort(404, __('Backup file not found.'));
        }

        // Get real paths to compare (follows symlinks)
        $realPathResolved = realpath($realPath);
        $backupsPathResolved = realpath($backupsPath);

        // Check if the file is actually within the backups directory
        if ($realPathResolved === false || $backupsPathResolved === false) {
            \Log::error('Path resolution failed in backup download', [
                'filename' => $filename,
                'real_path' => $realPath,
                'backups_path' => $backupsPath,
            ]);
            abort(500, __('Internal server error.'));
        }

        if (strpos($realPathResolved, $backupsPathResolved) !== 0) {
            \Log::warning('Backup download attempt outside allowed directory', [
                'filename' => $filename,
                'resolved_path' => $realPathResolved,
                'allowed_path' => $backupsPathResolved,
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
            ]);
            abort(403, __('Access denied.'));
        }

        // Log successful download for audit trail
        \Log::info('Backup file downloaded', [
            'filename' => $safeFilename,
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'ip' => request()->ip(),
        ]);

        return Storage::disk('local')->download($path, $safeFilename, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Import database from uploaded JSON file
     *
     * Security: Reduced file size limit and added JSON structure validation
     * to prevent DoS attacks and malformed data imports.
     */
    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json|max:10240', // 10MB max (reduced from 50MB)
            'mode' => 'required|in:merge,replace',
        ]);

        $file = $request->file('backup_file');
        $mode = $request->input('mode');

        // Security: Validate JSON structure before processing
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::warning('Invalid JSON in backup import attempt', [
                'user_id' => auth()->id(),
                'error' => json_last_error_msg(),
            ]);
            return back()->with('error', __('Invalid JSON format: :error', ['error' => json_last_error_msg()]));
        }

        // Validate expected backup structure
        $requiredKeys = ['version', 'created_at', 'tables'];
        foreach ($requiredKeys as $key) {
            if (!isset($data[$key])) {
                \Log::warning('Backup import missing required key', [
                    'user_id' => auth()->id(),
                    'missing_key' => $key,
                ]);
                return back()->with('error', __('Invalid backup file: missing required field ":key"', ['key' => $key]));
            }
        }

        // Validate tables is an array
        if (!is_array($data['tables'])) {
            return back()->with('error', __('Invalid backup file: tables must be an array.'));
        }

        // Save uploaded file temporarily
        $tempFilename = 'temp_restore_' . time() . '.json';
        $tempPath = 'backups/' . $tempFilename;
        Storage::disk('local')->put($tempPath, $content);

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

        // SECURITY: Apply same filename validation as download
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
            return back()->with('error', __('Invalid filename format.'));
        }

        $safeFilename = basename($filename);
        $mode = $request->input('mode');

        // Validate backup file exists
        $validation = $this->backupService->validateBackupFile($safeFilename);

        if (!$validation['exists']) {
            return back()->with('error', __('Backup not found.'));
        }

        if (!$validation['valid']) {
            return back()->with('error', __('Invalid backup file format.'));
        }

        // Perform restore
        $result = $this->restoreService->restoreFromBackup($safeFilename, $mode);

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
        // SECURITY: Apply same filename validation
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
            return back()->with('error', __('Invalid filename format.'));
        }

        $safeFilename = basename($filename);
        $deleted = $this->backupService->deleteBackup($safeFilename);

        if ($deleted) {
            \Log::info('Backup file deleted', [
                'filename' => $safeFilename,
                'user_id' => auth()->id(),
            ]);
            return back()->with('success', __('Backup deleted successfully.'));
        }

        return back()->with('error', __('Backup not found.'));
    }
}
