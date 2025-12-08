<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * These indexes improve query performance for common filtering and search operations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Add index for filtering by status (active/paused/cancelled)
            $table->index('status', 'subscriptions_status_index');

            // Add compound index for user dashboard queries (user + status filter)
            $table->index(['user_id', 'status'], 'subscriptions_user_id_status_compound_index');
        });

        Schema::table('access_credentials', function (Blueprint $table) {
            // Add index for filtering by platform
            $table->index('platform', 'access_credentials_platform_index');

            // Add index for last accessed queries (sorting/filtering by access time)
            $table->index('last_accessed_at', 'access_credentials_last_accessed_at_index');
        });

        Schema::table('internal_accounts', function (Blueprint $table) {
            // Add index for filtering by platform
            $table->index('platforma', 'internal_accounts_platform_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('subscriptions_status_index');
            $table->dropIndex('subscriptions_user_id_status_compound_index');
        });

        Schema::table('access_credentials', function (Blueprint $table) {
            $table->dropIndex('access_credentials_platform_index');
            $table->dropIndex('access_credentials_last_accessed_at_index');
        });

        Schema::table('internal_accounts', function (Blueprint $table) {
            $table->dropIndex('internal_accounts_platform_index');
        });
    }
};
