<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Additional indexes for performance optimization.
     * Checks for existing indexes to avoid duplicates.
     */
    public function up(): void
    {
        // Index for SettingOptions - queried by organization and category
        if (!$this->indexExists('settings_options', 'idx_settings_org_category')) {
            Schema::table('settings_options', function (Blueprint $table) {
                $table->index(['organization_id', 'category'], 'idx_settings_org_category');
            });
        }

        // Index for domains - organization + expiry for notification queries
        if (!$this->indexExists('domains', 'idx_domains_org_expiry')) {
            Schema::table('domains', function (Blueprint $table) {
                $table->index(['organization_id', 'expiry_date'], 'idx_domains_org_expiry');
            });
        }

        // Index for subscriptions - user + renewal for notification queries
        if (!$this->indexExists('subscriptions', 'idx_subscriptions_user_renewal')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->index(['user_id', 'next_renewal_date'], 'idx_subscriptions_user_renewal');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->indexExists('settings_options', 'idx_settings_org_category')) {
            Schema::table('settings_options', function (Blueprint $table) {
                $table->dropIndex('idx_settings_org_category');
            });
        }

        if ($this->indexExists('domains', 'idx_domains_org_expiry')) {
            Schema::table('domains', function (Blueprint $table) {
                $table->dropIndex('idx_domains_org_expiry');
            });
        }

        if ($this->indexExists('subscriptions', 'idx_subscriptions_user_renewal')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropIndex('idx_subscriptions_user_renewal');
            });
        }
    }

    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
