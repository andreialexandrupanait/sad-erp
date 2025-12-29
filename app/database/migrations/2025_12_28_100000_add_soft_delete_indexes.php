<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
            // Check if table exists
            $tableExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
                AND table_name = ?
            ", [$table]);

            if ($tableExists[0]->count == 0) {
                continue; // Skip if table doesn't exist
            }

            // Check if index exists before creating it
            $indexExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = ?
                AND column_name = 'deleted_at'
            ", [$table]);

            if ($indexExists[0]->count == 0) {
                // Check if column exists
                $columnExists = DB::select("
                    SELECT COUNT(*) as count
                    FROM information_schema.columns
                    WHERE table_schema = DATABASE()
                    AND table_name = ?
                    AND column_name = 'deleted_at'
                ", [$table]);

                if ($columnExists[0]->count > 0) {
                    DB::statement("ALTER TABLE `{$table}` ADD INDEX `idx_{$table}_deleted_at` (`deleted_at`)");
                }
            }
        }
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
            DB::statement("ALTER TABLE `{$table}` DROP INDEX IF EXISTS `idx_{$table}_deleted_at`");
        }
    }
};
