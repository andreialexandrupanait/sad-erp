<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use ZipArchive;

class BackupController extends Controller
{
    public function __construct()
    {
        // Only admins can access backup functionality
        $this->middleware(function ($request, $next) {
            if (!in_array(auth()->user()->role, ['admin', 'superadmin'])) {
                abort(403, __('Only administrators can manage backups.'));
            }
            return $next($request);
        });
    }

    /**
     * Tables to include in backup (core business data).
     */
    protected array $backupTables = [
        'organizations',
        'users',
        'clients',
        'domains',
        'subscriptions',
        'subscription_logs',
        'internal_accounts',
        'access_credentials',
        'financial_revenues',
        'financial_expenses',
        'financial_files',
        'recurring_expenses',
        'financial_alerts',
        'settings_options',
        'settings_app',
        'services',
        'smartbill_imports',
        'notification_logs',
    ];

    /**
     * Show backup management page.
     */
    public function index()
    {
        $backups = $this->getExistingBackups();

        return view('settings.backup.index', [
            'backups' => $backups,
            'tables' => $this->getAvailableTables(),
        ]);
    }

    /**
     * Export database to JSON file.
     */
    public function export(Request $request)
    {
        $request->validate([
            'tables' => 'nullable|array',
            'tables.*' => 'string',
        ]);

        $tables = $request->input('tables', $this->backupTables);
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = "backup_{$timestamp}.json";

        $data = [
            'meta' => [
                'created_at' => Carbon::now()->toIso8601String(),
                'version' => '1.0',
                'tables' => $tables,
            ],
            'data' => [],
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $data['data'][$table] = DB::table($table)->get()->toArray();
            }
        }

        // Store backup
        $path = 'backups/' . $filename;
        Storage::disk('local')->put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json([
            'success' => true,
            'message' => __('Backup created successfully.'),
            'filename' => $filename,
            'download_url' => route('settings.backup.download', $filename),
        ]);
    }

    /**
     * Download a backup file.
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
     * Import database from JSON file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json|max:51200', // 50MB max
            'mode' => 'required|in:merge,replace',
        ]);

        $file = $request->file('backup_file');
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (!$data || !isset($data['data'])) {
            return back()->with('error', __('Invalid backup file format.'));
        }

        $mode = $request->input('mode');
        $imported = [];
        $errors = [];

        try {
            // Disable foreign key checks for the entire operation
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($data['data'] as $table => $rows) {
                if (!Schema::hasTable($table)) {
                    $errors[] = __('Table :table does not exist, skipped.', ['table' => $table]);
                    continue;
                }

                if ($mode === 'replace') {
                    // Use DELETE instead of TRUNCATE to avoid implicit commits
                    DB::table($table)->delete();
                }

                if (empty($rows)) {
                    $imported[$table] = 0;
                    continue;
                }

                $count = 0;
                foreach ($rows as $row) {
                    $rowData = (array) $row;

                    if ($mode === 'merge') {
                        // Try to update existing or insert new
                        if (isset($rowData['id'])) {
                            $existing = DB::table($table)->where('id', $rowData['id'])->exists();
                            if ($existing) {
                                DB::table($table)->where('id', $rowData['id'])->update($rowData);
                            } else {
                                DB::table($table)->insert($rowData);
                            }
                        } else {
                            DB::table($table)->insert($rowData);
                        }
                    } else {
                        DB::table($table)->insert($rowData);
                    }
                    $count++;
                }

                $imported[$table] = $count;
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $message = __('Import completed successfully.');
            if (!empty($errors)) {
                $message .= ' ' . __('Some tables were skipped.');
            }

            return back()->with('success', $message)->with('import_results', [
                'imported' => $imported,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            // Re-enable foreign key checks even on error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return back()->with('error', __('Import failed: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Restore from an existing backup file.
     */
    public function restore(Request $request, string $filename)
    {
        $request->validate([
            'mode' => 'required|in:merge,replace',
        ]);

        $path = 'backups/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            return back()->with('error', __('Backup not found.'));
        }

        $content = Storage::disk('local')->get($path);
        $data = json_decode($content, true);

        if (!$data || !isset($data['data'])) {
            return back()->with('error', __('Invalid backup file format.'));
        }

        $mode = $request->input('mode');
        $imported = [];
        $errors = [];

        try {
            // Disable foreign key checks for the entire operation
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($data['data'] as $table => $rows) {
                if (!Schema::hasTable($table)) {
                    $errors[] = __('Table :table does not exist, skipped.', ['table' => $table]);
                    continue;
                }

                if ($mode === 'replace') {
                    // Use DELETE instead of TRUNCATE to avoid implicit commits
                    DB::table($table)->delete();
                }

                if (empty($rows)) {
                    $imported[$table] = 0;
                    continue;
                }

                $count = 0;
                foreach ($rows as $row) {
                    $rowData = (array) $row;

                    if ($mode === 'merge') {
                        if (isset($rowData['id'])) {
                            $existing = DB::table($table)->where('id', $rowData['id'])->exists();
                            if ($existing) {
                                DB::table($table)->where('id', $rowData['id'])->update($rowData);
                            } else {
                                DB::table($table)->insert($rowData);
                            }
                        } else {
                            DB::table($table)->insert($rowData);
                        }
                    } else {
                        DB::table($table)->insert($rowData);
                    }
                    $count++;
                }

                $imported[$table] = $count;
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $message = __('Restore completed successfully.');
            if (!empty($errors)) {
                $message .= ' ' . __('Some tables were skipped.');
            }

            return back()->with('success', $message)->with('import_results', [
                'imported' => $imported,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            // Re-enable foreign key checks even on error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return back()->with('error', __('Restore failed: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Delete a backup file.
     */
    public function destroy(string $filename)
    {
        $path = 'backups/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            return back()->with('error', __('Backup not found.'));
        }

        Storage::disk('local')->delete($path);

        return back()->with('success', __('Backup deleted successfully.'));
    }

    /**
     * Get list of existing backups.
     */
    protected function getExistingBackups(): array
    {
        $backups = [];
        $files = Storage::disk('local')->files('backups');

        foreach ($files as $file) {
            $filename = basename($file);
            if (str_ends_with($filename, '.json')) {
                $content = Storage::disk('local')->get($file);
                $data = json_decode($content, true);

                $backups[] = [
                    'filename' => $filename,
                    'size' => Storage::disk('local')->size($file),
                    'created_at' => $data['meta']['created_at'] ?? null,
                    'tables' => $data['meta']['tables'] ?? [],
                ];
            }
        }

        // Sort by newest first
        usort($backups, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        return $backups;
    }

    /**
     * Get list of available tables for backup.
     */
    protected function getAvailableTables(): array
    {
        $tables = [];
        foreach ($this->backupTables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $tables[] = [
                    'name' => $table,
                    'count' => $count,
                ];
            }
        }
        return $tables;
    }
}
