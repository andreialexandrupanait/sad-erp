<?php

namespace App\Services\Database;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DatabaseBackupService
{
    /**
     * Default tables to include in backup (core business data)
     */
    protected array $defaultBackupTables = [
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
     * Create a database backup
     *
     * @param array|null $tables Tables to backup (null = use defaults)
     * @return array ['success' => bool, 'filename' => string, 'path' => string]
     */
    public function createBackup(?array $tables = null): array
    {
        $tables = $tables ?? $this->defaultBackupTables;
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

        // Export each table
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $data['data'][$table] = DB::table($table)->get()->toArray();
            }
        }

        // Store backup
        $path = 'backups/' . $filename;
        Storage::disk('local')->put(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return [
            'success' => true,
            'filename' => $filename,
            'path' => $path,
            'size' => Storage::disk('local')->size($path),
            'tables_count' => count($data['data']),
        ];
    }

    /**
     * Get list of all existing backups
     *
     * @return array
     */
    public function getExistingBackups(): array
    {
        $files = Storage::disk('local')->files('backups');
        $backups = [];

        foreach ($files as $file) {
            if (!str_ends_with($file, '.json')) {
                continue;
            }

            $filename = basename($file);
            $backups[] = [
                'filename' => $filename,
                'path' => $file,
                'size' => Storage::disk('local')->size($file),
                'size_human' => $this->formatBytes(Storage::disk('local')->size($file)),
                'created_at' => Carbon::createFromTimestamp(Storage::disk('local')->lastModified($file)),
            ];
        }

        // Sort by creation date descending
        usort($backups, fn($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        return $backups;
    }

    /**
     * Get all available database tables
     *
     * @return array
     */
    public function getAvailableTables(): array
    {
        $tables = DB::select('SHOW TABLES');
        $databaseName = DB::getDatabaseName();
        $columnName = "Tables_in_{$databaseName}";

        return array_map(fn($table) => $table->$columnName, $tables);
    }

    /**
     * Delete a backup file
     *
     * @param string $filename
     * @return bool
     */
    public function deleteBackup(string $filename): bool
    {
        $path = 'backups/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            return false;
        }

        return Storage::disk('local')->delete($path);
    }

    /**
     * Check if backup file exists and is valid
     *
     * @param string $filename
     * @return array ['exists' => bool, 'valid' => bool, 'meta' => array|null]
     */
    public function validateBackupFile(string $filename): array
    {
        $path = 'backups/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            return ['exists' => false, 'valid' => false, 'meta' => null];
        }

        $content = Storage::disk('local')->get($path);
        $data = json_decode($content, true);

        $isValid = $data && isset($data['data']) && isset($data['meta']);

        return [
            'exists' => true,
            'valid' => $isValid,
            'meta' => $isValid ? $data['meta'] : null,
            'tables_count' => $isValid ? count($data['data']) : 0,
        ];
    }

    /**
     * Format bytes to human-readable format
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
