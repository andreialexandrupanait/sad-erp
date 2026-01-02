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
     * Chunk size for database exports to prevent memory exhaustion
     */
    protected int $chunkSize = 1000;

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
        $path = 'backups/' . $filename;

        $meta = [
            'created_at' => Carbon::now()->toIso8601String(),
            'version' => '1.1',
            'tables' => $tables,
        ];

        // Stream JSON to file to prevent memory exhaustion
        $this->streamBackupToFile($path, $meta, $tables);

        return [
            'success' => true,
            'filename' => $filename,
            'path' => $path,
            'size' => Storage::disk('local')->size($path),
            'tables_count' => count($tables),
        ];
    }

    /**
     * Stream backup data to file using chunking to prevent memory exhaustion
     */
    protected function streamBackupToFile(string $path, array $meta, array $tables): void
    {
        $fullPath = Storage::disk('local')->path($path);

        // Ensure directory exists
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $handle = fopen($fullPath, 'w');
        if ($handle === false) {
            throw new \RuntimeException("Cannot open file for writing: {$path}");
        }

        try {
            // Write JSON structure start
            fwrite($handle, "{\n");
            fwrite($handle, '  "meta": ' . json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            fwrite($handle, ",\n  \"data\": {\n");

            $tableIndex = 0;
            $tableCount = count($tables);

            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                // Write table name
                fwrite($handle, '    "' . $table . '": [');

                // Export table data in chunks
                $rowIndex = 0;
                DB::table($table)->orderBy(DB::raw('1'))->chunk($this->chunkSize, function ($rows) use ($handle, &$rowIndex) {
                    foreach ($rows as $row) {
                        if ($rowIndex > 0) {
                            fwrite($handle, ',');
                        }
                        fwrite($handle, "\n      " . json_encode((array) $row, JSON_UNESCAPED_UNICODE));
                        $rowIndex++;
                    }
                });

                fwrite($handle, "\n    ]");

                // Add comma if not last table
                $tableIndex++;
                if ($tableIndex < $tableCount) {
                    fwrite($handle, ',');
                }
                fwrite($handle, "\n");
            }

            // Close JSON structure
            fwrite($handle, "  }\n}");
        } finally {
            fclose($handle);
        }
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
