<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add performance indexes for common query patterns.
 *
 * These indexes optimize:
 * - Dashboard widgets and metrics queries
 * - Financial reporting aggregations
 * - Client/credential lookups
 * - Subscription renewal queries
 * - Domain expiry queries
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clients table - commonly filtered by status and sorted by updated_at
        $this->addIndexIfNotExists('clients', 'status_id', 'clients_status_id_index');
        $this->addIndexIfNotExists('clients', 'updated_at', 'clients_updated_at_index');

        // Access credentials - grouped by client and site
        $this->addIndexIfNotExists('access_credentials', 'client_id', 'access_credentials_client_id_index');
        $this->addIndexIfNotExists('access_credentials', 'site_name', 'access_credentials_site_name_index');

        // Domains - frequently queried by expiry date
        $this->addIndexIfNotExists('domains', 'expiry_date', 'domains_expiry_date_index');
        $this->addIndexIfNotExists('domains', 'client_id', 'domains_client_id_index');

        // Subscriptions - renewal date queries for upcoming/overdue
        $this->addIndexIfNotExists('subscriptions', 'next_renewal_date', 'subscriptions_next_renewal_date_index');
        $this->addCompositeIndexIfNotExists('subscriptions', ['status', 'next_renewal_date'], 'subscriptions_status_renewal_index');

        // Financial revenues - aggregations by year/currency
        $this->addCompositeIndexIfNotExists('financial_revenues', ['year', 'currency'], 'financial_revenues_year_currency_index');
        $this->addIndexIfNotExists('financial_revenues', 'client_id', 'financial_revenues_client_id_index');
        $this->addCompositeIndexIfNotExists('financial_revenues', ['year', 'month'], 'financial_revenues_year_month_index');

        // Financial expenses - aggregations by year/currency
        $this->addCompositeIndexIfNotExists('financial_expenses', ['year', 'currency'], 'financial_expenses_year_currency_index');
        $this->addCompositeIndexIfNotExists('financial_expenses', ['year', 'month'], 'financial_expenses_year_month_index');
        $this->addIndexIfNotExists('financial_expenses', 'category_option_id', 'financial_expenses_category_index');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('clients', 'clients_status_id_index');
        $this->dropIndexIfExists('clients', 'clients_updated_at_index');

        $this->dropIndexIfExists('access_credentials', 'access_credentials_client_id_index');
        $this->dropIndexIfExists('access_credentials', 'access_credentials_site_name_index');

        $this->dropIndexIfExists('domains', 'domains_expiry_date_index');
        $this->dropIndexIfExists('domains', 'domains_client_id_index');

        $this->dropIndexIfExists('subscriptions', 'subscriptions_next_renewal_date_index');
        $this->dropIndexIfExists('subscriptions', 'subscriptions_status_renewal_index');

        $this->dropIndexIfExists('financial_revenues', 'financial_revenues_year_currency_index');
        $this->dropIndexIfExists('financial_revenues', 'financial_revenues_client_id_index');
        $this->dropIndexIfExists('financial_revenues', 'financial_revenues_year_month_index');

        $this->dropIndexIfExists('financial_expenses', 'financial_expenses_year_currency_index');
        $this->dropIndexIfExists('financial_expenses', 'financial_expenses_year_month_index');
        $this->dropIndexIfExists('financial_expenses', 'financial_expenses_category_index');
    }

    /**
     * Add a single-column index if it doesn't exist.
     */
    private function addIndexIfNotExists(string $table, string $column, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $table) use ($column, $indexName) {
                $table->index($column, $indexName);
            });
        }
    }

    /**
     * Add a composite index if it doesn't exist.
     */
    private function addCompositeIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        }
    }

    /**
     * Drop an index if it exists.
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }

    /**
     * Check if an index exists using raw SQL.
     * Supports both MySQL and SQLite.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $result = DB::select(
                "SELECT name FROM sqlite_master WHERE type = 'index' AND name = ?",
                [$indexName]
            );
        } else {
            // MySQL/MariaDB
            $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        }

        return count($result) > 0;
    }
};
