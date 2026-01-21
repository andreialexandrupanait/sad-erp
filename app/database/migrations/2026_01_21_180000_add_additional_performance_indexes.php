<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Add additional performance indexes identified in PERFORMANCE_ANALYSIS.md report.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // Skip for SQLite as it handles indexes differently during tests
        if ($driver === 'sqlite') {
            return;
        }

        // Credentials table - for LIKE searches on site_name and website
        $this->addIndexSafely('credentials', 'site_name', 'credentials_site_name_index');
        $this->addIndexSafely('credentials', 'website', 'credentials_website_index');

        // Financial revenues - additional indexes for filtering
        $this->addIndexSafely('financial_revenues', 'is_archived', 'financial_revenues_is_archived_index');
        $this->addIndexSafely('financial_revenues', 'category_option_id', 'financial_revenues_category_option_id_index');

        // Financial files - composite index for year/month queries
        $this->addIndexSafely('financial_files', ['an', 'luna'], 'financial_files_an_luna_index');

        // Domains - for expiry date range queries
        $this->addIndexSafely('domains', 'expiry_date', 'domains_expiry_date_index');

        // Subscriptions - for renewal date queries
        $this->addIndexSafely('subscriptions', 'next_renewal_date', 'subscriptions_next_renewal_date_index');
        $this->addIndexSafely('subscriptions', ['status', 'next_renewal_date'], 'subscriptions_status_renewal_index');
    }

    /**
     * Add an index safely, ignoring if it already exists.
     */
    private function addIndexSafely(string $table, string|array $columns, string $indexName): void
    {
        // Check if table exists first
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        } catch (\Exception $e) {
            // Index already exists or other error, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        $this->dropIndexSafely('credentials', 'credentials_site_name_index');
        $this->dropIndexSafely('credentials', 'credentials_website_index');
        $this->dropIndexSafely('financial_revenues', 'financial_revenues_is_archived_index');
        $this->dropIndexSafely('financial_revenues', 'financial_revenues_category_option_id_index');
        $this->dropIndexSafely('financial_files', 'financial_files_an_luna_index');
        $this->dropIndexSafely('domains', 'domains_expiry_date_index');
        $this->dropIndexSafely('subscriptions', 'subscriptions_next_renewal_date_index');
        $this->dropIndexSafely('subscriptions', 'subscriptions_status_renewal_index');
    }

    /**
     * Drop an index safely, ignoring if it doesn't exist.
     */
    private function dropIndexSafely(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropIndex($indexName);
            });
        } catch (\Exception $e) {
            // Index doesn't exist or other error, ignore
        }
    }
};
