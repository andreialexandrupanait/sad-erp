<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

        $indexes = $connection->select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Run the migrations.
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
            if (!Schema::hasTable($index['table']) || !Schema::hasColumn($index['table'], $index['column'])) {
                continue;
            }

            if (!$this->indexExists($index['table'], $index['name'])) {
                try {
                    Schema::table($index['table'], function (Blueprint $table) use ($index) {
                        $table->index($index['column'], $index['name']);
                    });
                } catch (\Exception $e) {
                    // Index might already exist
                }
            }
        }

        // Composite indexes
        $compositeIndexes = [
            ['table' => 'financial_revenues', 'columns' => ['organization_id', 'occurred_at'], 'name' => 'idx_revenues_org_date'],
            ['table' => 'financial_expenses', 'columns' => ['organization_id', 'occurred_at'], 'name' => 'idx_expenses_org_date'],
        ];

        foreach ($compositeIndexes as $index) {
            if (!Schema::hasTable($index['table'])) {
                continue;
            }

            if (!$this->indexExists($index['table'], $index['name'])) {
                try {
                    Schema::table($index['table'], function (Blueprint $table) use ($index) {
                        $table->index($index['columns'], $index['name']);
                    });
                } catch (\Exception $e) {
                    // Index might already exist
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = [
            ['table' => 'financial_revenues', 'name' => 'idx_financial_revenues_occurred_at'],
            ['table' => 'financial_expenses', 'name' => 'idx_financial_expenses_occurred_at'],
            ['table' => 'bank_transactions', 'name' => 'idx_bank_transactions_date'],
            ['table' => 'offers', 'name' => 'idx_offers_valid_until'],
            ['table' => 'offers', 'name' => 'idx_offers_created_at'],
            ['table' => 'contracts', 'name' => 'idx_contracts_start_date'],
            ['table' => 'contracts', 'name' => 'idx_contracts_end_date'],
            ['table' => 'subscriptions', 'name' => 'idx_subscriptions_renewal_date'],
            ['table' => 'subscriptions', 'name' => 'idx_subscriptions_start_date'],
            ['table' => 'domains', 'name' => 'idx_domains_expiry_date'],
            ['table' => 'domains', 'name' => 'idx_domains_registration_date'],
            ['table' => 'recurring_expenses', 'name' => 'idx_recurring_expenses_due_date'],
            ['table' => 'financial_revenues', 'name' => 'idx_revenues_org_date'],
            ['table' => 'financial_expenses', 'name' => 'idx_expenses_org_date'],
        ];

        foreach ($indexes as $index) {
            if (!Schema::hasTable($index['table'])) {
                continue;
            }

            if ($this->indexExists($index['table'], $index['name'])) {
                try {
                    Schema::table($index['table'], function (Blueprint $table) use ($index) {
                        $table->dropIndex($index['name']);
                    });
                } catch (\Exception $e) {
                    // Ignore
                }
            }
        }
    }
};
