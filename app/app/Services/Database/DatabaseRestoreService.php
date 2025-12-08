<?php

namespace App\Services\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DatabaseRestoreService
{
    /**
     * Restore database from backup file
     *
     * @param string $filename Backup filename
     * @param string $mode 'merge' or 'replace'
     * @return array ['success' => bool, 'imported' => array, 'errors' => array]
     */
    public function restoreFromBackup(string $filename, string $mode = 'merge'): array
    {
        $path = 'backups/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
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
            return [
                'success' => false,
                'error' => 'Invalid backup file format',
                'imported' => [],
                'errors' => [],
            ];
        }

        return $this->performRestore($data['data'], $mode);
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

        try {
            // Disable foreign key checks for the entire operation
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($tables as $table => $rows) {
                $result = $this->restoreTable($table, $rows, $mode);

                if ($result['success']) {
                    $imported[$table] = $result['count'];
                } else {
                    $errors[] = $result['error'];
                }
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return [
                'success' => true,
                'imported' => $imported,
                'errors' => $errors,
            ];

        } catch (\Exception $e) {
            // Re-enable foreign key checks even on error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'imported' => $imported,
                'errors' => $errors,
            ];
        }
    }

    /**
     * Restore a single table
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

        $count = 0;

        foreach ($rows as $row) {
            $rowData = (array) $row;

            if ($mode === 'merge') {
                $count += $this->mergeRow($table, $rowData);
            } else {
                DB::table($table)->insert($rowData);
                $count++;
            }
        }

        return [
            'success' => true,
            'count' => $count,
        ];
    }

    /**
     * Merge a single row (insert or update based on existence)
     *
     * @param string $table
     * @param array $rowData
     * @return int Number of rows affected (0 or 1)
     */
    protected function mergeRow(string $table, array $rowData): int
    {
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

        return 1;
    }

    /**
     * Preview what would be restored without actually restoring
     *
     * @param string $filename
     * @return array
     */
    public function previewRestore(string $filename): array
    {
        $path = 'backups/' . $filename;

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

        $preview = [];

        foreach ($data['data'] as $table => $rows) {
            $preview[$table] = [
                'exists' => Schema::hasTable($table),
                'rows_in_backup' => count($rows),
                'rows_in_database' => Schema::hasTable($table) ? DB::table($table)->count() : 0,
            ];
        }

        return [
            'success' => true,
            'meta' => $data['meta'] ?? [],
            'tables' => $preview,
        ];
    }
}
