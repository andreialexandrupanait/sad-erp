<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add indexes on date columns used for filtering and sorting.
     * This improves performance for financial reports, expiry checks, and date-based queries.
     */
    public function up(): void
    {
        $indexes = [
            ['table' => 'financial_revenues', 'column' => 'occurred_at', 'name' => 'idx_financial_revenues_occurred_at'],
            ['table' => 'financial_expenses', 'column' => 'occurred_at', 'name' => 'idx_financial_expenses_occurred_at'],
            ['table' => 'bank_transactions', 'column' => 'transaction_date', 'name' => 'idx_bank_transactions_date'],
            ['table' => 'offers', 'column' => 'valid_until', 'name' => 'idx_offers_valid_until'],
            ['table' => 'offers', 'column' => 'created_at', 'name' => 'idx_offers_created_at'],
            ['table' => 'contracts', 'column' => 'start_date', 'name' => 'idx_contracts_start_date'],
            ['table' => 'contracts', 'column' => 'end_date', 'name' => 'idx_contracts_end_date'],
            ['table' => 'subscriptions', 'column' => 'next_renewal_date', 'name' => 'idx_subscriptions_renewal_date'],
            ['table' => 'subscriptions', 'column' => 'start_date', 'name' => 'idx_subscriptions_start_date'],
            ['table' => 'domains', 'column' => 'expiry_date', 'name' => 'idx_domains_expiry_date'],
            ['table' => 'domains', 'column' => 'registration_date', 'name' => 'idx_domains_registration_date'],
            ['table' => 'recurring_expenses', 'column' => 'next_due_date', 'name' => 'idx_recurring_expenses_due_date'],
        ];

        foreach ($indexes as $index) {
            // Check if table exists
            $tableExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
                AND table_name = ?
            ", [$index['table']]);

            if ($tableExists[0]->count == 0) {
                continue; // Skip if table doesn't exist
            }

            // Check if index already exists
            $exists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = ?
                AND index_name = ?
            ", [$index['table'], $index['name']]);

            if ($exists[0]->count == 0) {
                // Check if column exists
                $columnExists = DB::select("
                    SELECT COUNT(*) as count
                    FROM information_schema.columns
                    WHERE table_schema = DATABASE()
                    AND table_name = ?
                    AND column_name = ?
                ", [$index['table'], $index['column']]);

                if ($columnExists[0]->count > 0) {
                    DB::statement("ALTER TABLE `{$index['table']}` ADD INDEX `{$index['name']}` (`{$index['column']}`)");
                }
            }
        }

        // Composite indexes for financial queries by organization and date
        $compositeIndexes = [
            ['table' => 'financial_revenues', 'columns' => 'organization_id, occurred_at', 'name' => 'idx_revenues_org_date'],
            ['table' => 'financial_expenses', 'columns' => 'organization_id, occurred_at', 'name' => 'idx_expenses_org_date'],
        ];

        foreach ($compositeIndexes as $index) {
            // Check if table exists
            $tableExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
                AND table_name = ?
            ", [$index['table']]);

            if ($tableExists[0]->count == 0) {
                continue; // Skip if table doesn't exist
            }

            $exists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = ?
                AND index_name = ?
            ", [$index['table'], $index['name']]);

            if ($exists[0]->count == 0) {
                DB::statement("ALTER TABLE `{$index['table']}` ADD INDEX `{$index['name']}` ({$index['columns']})");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexNames = [
            'idx_financial_revenues_occurred_at',
            'idx_financial_expenses_occurred_at',
            'idx_bank_transactions_date',
            'idx_offers_valid_until',
            'idx_offers_created_at',
            'idx_contracts_start_date',
            'idx_contracts_end_date',
            'idx_subscriptions_renewal_date',
            'idx_subscriptions_start_date',
            'idx_domains_expiry_date',
            'idx_domains_registration_date',
            'idx_recurring_expenses_due_date',
            'idx_revenues_org_date',
            'idx_expenses_org_date',
        ];

        $tables = [
            'financial_revenues', 'financial_expenses', 'bank_transactions',
            'offers', 'contracts', 'subscriptions', 'domains', 'recurring_expenses'
        ];

        foreach ($tables as $table) {
            foreach ($indexNames as $indexName) {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX IF EXISTS `{$indexName}`");
            }
        }
    }
};
