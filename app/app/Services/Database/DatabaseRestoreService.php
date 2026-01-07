<?php

namespace App\Services\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DatabaseRestoreService
{
    /**
     * SECURITY: Whitelist of tables that can be restored
     * Only these tables are allowed in backup restore operations
     * 
     * Add new tables here as your application grows
     */
    protected array $allowedTables = [
        // Core tables
        'users',
        'organizations',
        
        // Client management
        'clients',
        'access_credentials',
        
        // Financial tables
        'financial_revenues',
        'financial_expenses',
        'financial_files',
        
        // Banking
        'banking_credentials',
        'bank_transactions',
        
        // Domains and services
        'domains',
        'subscriptions',
        'services',

        // Settings and configuration
        'settings_options',
        'modules',
        'user_module_permissions',
        'role_module_defaults',
        
        // Add more tables as needed
        // DO NOT add: password_resets, sessions, cache, jobs (system tables)
    ];

    /**
     * Tables that should NEVER be restored (system/security tables)
     */
    protected array $forbiddenTables = [
        'password_resets',
        'password_reset_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'migrations',
        'personal_access_tokens',
    ];

    /**
     * Batch size for insert/upsert operations to prevent memory exhaustion
     */
    protected int $batchSize = 500;

    /**
     * Restore database from backup file
     *
     * @param string $filename Backup filename
     * @param string $mode 'merge' or 'replace'
     * @return array ['success' => bool, 'imported' => array, 'errors' => array]
     */
    public function restoreFromBackup(string $filename, string $mode = 'merge'): array
    {
        // SECURITY: Use basename to prevent path traversal
        $safeFilename = basename($filename);
        $path = 'backups/' . $safeFilename;

        if (!Storage::disk('local')->exists($path)) {
            Log::warning('Restore attempted with non-existent file', [
                'filename' => $filename,
                'user_id' => auth()->id(),
            ]);
            return [
                'success' => false,
                'error' => 'Backup file not found',
                'imported' => [],
                'errors' => [],
            ];
        }

        $content = Storage::disk('local')->get($path);
        $data = json_decode($content, true);

        if (!$data || !isset($data['data'])) {
            Log::error('Invalid backup file format', [
                'filename' => $filename,
                'user_id' => auth()->id(),
            ]);
            return [
                'success' => false,
                'error' => 'Invalid backup file format',
                'imported' => [],
                'errors' => [],
            ];
        }

        // SECURITY: Validate all tables before starting restore
        $validationResult = $this->validateTables(array_keys($data['data']));
        if (!$validationResult['valid']) {
            Log::warning('Backup restore blocked: unauthorized tables', [
                'filename' => $filename,
                'unauthorized_tables' => $validationResult['unauthorized'],
                'forbidden_tables' => $validationResult['forbidden'],
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
            ]);
            return [
                'success' => false,
                'error' => 'Backup contains unauthorized tables: ' . 
                          implode(', ', array_merge(
                              $validationResult['unauthorized'], 
                              $validationResult['forbidden']
                          )),
                'imported' => [],
                'errors' => [],
            ];
        }

        Log::info('Starting database restore', [
            'filename' => $filename,
            'mode' => $mode,
            'tables' => array_keys($data['data']),
            'user_id' => auth()->id(),
        ]);

        return $this->performRestore($data['data'], $mode);
    }

    /**
     * SECURITY: Validate that all tables are whitelisted
     * 
     * @param array $tables List of table names to validate
     * @return array ['valid' => bool, 'unauthorized' => array, 'forbidden' => array]
     */
    protected function validateTables(array $tables): array
    {
        $unauthorized = [];
        $forbidden = [];

        foreach ($tables as $table) {
            // Check if table is explicitly forbidden
            if (in_array($table, $this->forbiddenTables)) {
                $forbidden[] = $table;
                continue;
            }

            // Check if table is in whitelist
            if (!in_array($table, $this->allowedTables)) {
                $unauthorized[] = $table;
            }
        }

        return [
            'valid' => empty($unauthorized) && empty($forbidden),
            'unauthorized' => $unauthorized,
            'forbidden' => $forbidden,
        ];
    }

    /**
     * Perform the actual restore operation
     *
     * @param array $tables
     * @param string $mode
     * @return array
     */
    protected function performRestore(array $tables, string $mode): array
    {
        $imported = [];
        $errors = [];
        $success = false;
        $errorMessage = null;

        try {
            DB::beginTransaction();

            // Disable foreign key checks for the entire operation
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($tables as $table => $rows) {
                // Double-check table is allowed (defense in depth)
                if (!in_array($table, $this->allowedTables)) {
                    $errors[] = "Table {$table} is not in whitelist, skipped";
                    continue;
                }

                $result = $this->restoreTable($table, $rows, $mode);

                if ($result['success']) {
                    $imported[$table] = $result['count'];
                    Log::info("Restored table: {$table}", [
                        'rows' => $result['count'],
                        'mode' => $mode,
                    ]);
                } else {
                    $errors[] = $result['error'];
                    Log::warning("Failed to restore table: {$table}", [
                        'error' => $result['error'],
                    ]);
                }
            }

            DB::commit();
            $success = true;

            Log::info('Database restore completed', [
                'imported_tables' => count($imported),
                'total_rows' => array_sum($imported),
                'errors' => count($errors),
                'user_id' => auth()->id(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage();

            Log::error('Database restore failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
        } finally {
            // Always re-enable foreign key checks, regardless of success or failure
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Exception $e) {
                Log::error('Failed to re-enable foreign key checks', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($success) {
            return [
                'success' => true,
                'imported' => $imported,
                'errors' => $errors,
            ];
        }

        return [
            'success' => false,
            'error' => $errorMessage,
            'imported' => $imported,
            'errors' => $errors,
        ];
    }

    /**
     * Restore a single table using batch operations
     *
     * @param string $table
     * @param array $rows
     * @param string $mode
     * @return array
     */
    protected function restoreTable(string $table, array $rows, string $mode): array
    {
        if (!Schema::hasTable($table)) {
            return [
                'success' => false,
                'error' => "Table {$table} does not exist, skipped",
                'count' => 0,
            ];
        }

        if ($mode === 'replace') {
            // Use DELETE instead of TRUNCATE to avoid implicit commits
            DB::table($table)->delete();
        }

        if (empty($rows)) {
            return [
                'success' => true,
                'count' => 0,
            ];
        }

        // Convert all rows to arrays
        $rows = array_map(fn($row) => (array) $row, $rows);

        if ($mode === 'merge') {
            return $this->batchMerge($table, $rows);
        }

        // For replace mode, use batch insert
        return $this->batchInsert($table, $rows);
    }

    /**
     * Batch insert rows into a table
     *
     * @param string $table
     * @param array $rows
     * @return array
     */
    protected function batchInsert(string $table, array $rows): array
    {
        $count = 0;
        $chunks = array_chunk($rows, $this->batchSize);

        foreach ($chunks as $chunk) {
            DB::table($table)->insert($chunk);
            $count += count($chunk);
        }

        return [
            'success' => true,
            'count' => $count,
        ];
    }

    /**
     * Batch merge (upsert) rows into a table
     *
     * @param string $table
     * @param array $rows
     * @return array
     */
    protected function batchMerge(string $table, array $rows): array
    {
        $count = 0;
        $chunks = array_chunk($rows, $this->batchSize);

        // Get column names from first row for the update clause
        $updateColumns = !empty($rows) ? array_keys($rows[0]) : [];
        // Remove 'id' from update columns (it's the unique key)
        $updateColumns = array_filter($updateColumns, fn($col) => $col !== 'id');

        foreach ($chunks as $chunk) {
            // Separate rows with and without IDs
            $withIds = array_filter($chunk, fn($row) => isset($row['id']));
            $withoutIds = array_filter($chunk, fn($row) => !isset($row['id']));

            // For rows with IDs, use upsert
            if (!empty($withIds)) {
                DB::table($table)->upsert(
                    array_values($withIds),
                    ['id'],  // Unique key
                    $updateColumns
                );
                $count += count($withIds);
            }

            // For rows without IDs, just insert
            if (!empty($withoutIds)) {
                DB::table($table)->insert(array_values($withoutIds));
                $count += count($withoutIds);
            }
        }

        return [
            'success' => true,
            'count' => $count,
        ];
    }

    /**
     * Preview what would be restored without actually restoring
     *
     * @param string $filename
     * @return array
     */
    public function previewRestore(string $filename): array
    {
        // SECURITY: Use basename to prevent path traversal
        $safeFilename = basename($filename);
        $path = 'backups/' . $safeFilename;

        if (!Storage::disk('local')->exists($path)) {
            return [
                'success' => false,
                'error' => 'Backup file not found',
            ];
        }

        $content = Storage::disk('local')->get($path);
        $data = json_decode($content, true);

        if (!$data || !isset($data['data'])) {
            return [
                'success' => false,
                'error' => 'Invalid backup file format',
            ];
        }

        // SECURITY: Validate tables
        $validationResult = $this->validateTables(array_keys($data['data']));

        $preview = [];

        foreach ($data['data'] as $table => $rows) {
            $isAllowed = in_array($table, $this->allowedTables);
            $isForbidden = in_array($table, $this->forbiddenTables);

            $preview[$table] = [
                'allowed' => $isAllowed && !$isForbidden,
                'exists' => Schema::hasTable($table),
                'rows_in_backup' => count($rows),
                'rows_in_database' => Schema::hasTable($table) ? DB::table($table)->count() : 0,
                'status' => $isForbidden ? 'FORBIDDEN' : ($isAllowed ? 'OK' : 'NOT WHITELISTED'),
            ];
        }

        return [
            'success' => true,
            'meta' => $data['meta'] ?? [],
            'tables' => $preview,
            'validation' => $validationResult,
        ];
    }

    /**
     * Get list of allowed tables (for documentation)
     * 
     * @return array
     */
    public function getAllowedTables(): array
    {
        return $this->allowedTables;
    }

    /**
     * Get list of forbidden tables (for documentation)
     * 
     * @return array
     */
    public function getForbiddenTables(): array
    {
        return $this->forbiddenTables;
    }
}
