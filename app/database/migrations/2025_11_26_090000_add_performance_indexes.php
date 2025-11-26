<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add indexes to improve query performance on frequently filtered/sorted columns.
     */
    public function up(): void
    {
        // Domains table indexes
        Schema::table('domains', function (Blueprint $table) {
            // Index for filtering by registrar
            if (!$this->hasIndex('domains', 'domains_registrar_index')) {
                $table->index('registrar', 'domains_registrar_index');
            }

            // Index for status filtering
            if (!$this->hasIndex('domains', 'domains_status_index')) {
                $table->index('status', 'domains_status_index');
            }
        });

        // Access credentials table indexes
        Schema::table('access_credentials', function (Blueprint $table) {
            // Index for platform filtering
            if (!$this->hasIndex('access_credentials', 'access_credentials_platform_index')) {
                $table->index('platform', 'access_credentials_platform_index');
            }

            // Index for last_accessed_at sorting
            if (!$this->hasIndex('access_credentials', 'access_credentials_last_accessed_at_index')) {
                $table->index('last_accessed_at', 'access_credentials_last_accessed_at_index');
            }
        });

        // Internal accounts table indexes
        Schema::table('internal_accounts', function (Blueprint $table) {
            // Index for account name search
            if (!$this->hasIndex('internal_accounts', 'internal_accounts_nume_cont_aplicatie_index')) {
                $table->index('nume_cont_aplicatie', 'internal_accounts_nume_cont_aplicatie_index');
            }
        });

        // Subscription logs table indexes
        Schema::table('subscription_logs', function (Blueprint $table) {
            // Index for faster log lookups by subscription
            if (!$this->hasIndex('subscription_logs', 'subscription_logs_subscription_id_changed_at_index')) {
                $table->index(['subscription_id', 'changed_at'], 'subscription_logs_subscription_id_changed_at_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropIndex('domains_registrar_index');
            $table->dropIndex('domains_status_index');
        });

        Schema::table('access_credentials', function (Blueprint $table) {
            $table->dropIndex('access_credentials_platform_index');
            $table->dropIndex('access_credentials_last_accessed_at_index');
        });

        Schema::table('internal_accounts', function (Blueprint $table) {
            $table->dropIndex('internal_accounts_nume_cont_aplicatie_index');
        });

        Schema::table('subscription_logs', function (Blueprint $table) {
            $table->dropIndex('subscription_logs_subscription_id_changed_at_index');
        });
    }

    /**
     * Check if an index exists on a table.
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if ($index['name'] === $indexName) {
                return true;
            }
        }
        return false;
    }
};
