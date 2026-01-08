<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add indexes on deleted_at columns for all tables using SoftDeletes.
     * This dramatically improves query performance for soft-deleted records.
     */
    public function up(): void
    {
        $tables = [
            'clients',
            'subscriptions',
            'domains',
            'offers',
            'contracts',
            'access_credentials',
            'financial_revenues',
            'financial_expenses',
            'recurring_expenses',
            'internal_accounts',
            'banking_credentials',
            'bank_transactions',
            'documents',
            'document_templates',
            'offer_templates',
            'contract_templates',
            'services',
        ];

        foreach ($tables as $table) {
            // Use Schema facade for database-agnostic checking
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (!Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            // Safely add index (ignore if already exists)
            $indexName = "idx_{$table}_deleted_at";
            if (!$this->indexExists($table, $indexName)) {
                try {
                    Schema::table($table, function (Blueprint $t) use ($indexName) {
                        $t->index('deleted_at', $indexName);
                    });
                } catch (\Exception $e) {
                    // Index might already exist, ignore
                }
            }
        }
    }

    /**
     * Check if an index exists (database-agnostic).
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = $connection->select(
                "SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = ? AND name = ?",
                [$table, $indexName]
            );
            return count($indexes) > 0;
        }

        // MySQL/MariaDB
        $indexes = $connection->select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'clients',
            'subscriptions',
            'domains',
            'offers',
            'contracts',
            'access_credentials',
            'financial_revenues',
            'financial_expenses',
            'recurring_expenses',
            'internal_accounts',
            'banking_credentials',
            'bank_transactions',
            'documents',
            'document_templates',
            'offer_templates',
            'contract_templates',
            'services',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $indexName = "idx_{$table}_deleted_at";
            if ($this->indexExists($table, $indexName)) {
                try {
                    Schema::table($table, function (Blueprint $t) use ($indexName) {
                        $t->dropIndex($indexName);
                    });
                } catch (\Exception $e) {
                    // Index might not exist, ignore
                }
            }
        }
    }
};
