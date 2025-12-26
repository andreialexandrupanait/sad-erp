<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add optimized indexes for credentials search and listing queries.
     */
    public function up(): void
    {
        // Add composite index for common filtering + sorting pattern
        // The index covers: organization_id (global scope), deleted_at (soft deletes),
        // site_name (grouping), credential_type (secondary sort), platform (tertiary sort)
        Schema::table('access_credentials', function (Blueprint $table) {
            // Composite index for main listing query with organization scope
            $table->index(
                ['organization_id', 'deleted_at', 'site_name', 'credential_type', 'platform'],
                'access_credentials_listing_idx'
            );
        });

        // Add fulltext index for faster search on text columns
        // Note: This only works with MySQL/MariaDB InnoDB tables
        try {
            DB::statement('ALTER TABLE access_credentials ADD FULLTEXT INDEX access_credentials_search_ft (site_name, platform, username, url)');
        } catch (\Exception $e) {
            // Fulltext may not be supported or already exists, continue silently
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_credentials', function (Blueprint $table) {
            $table->dropIndex('access_credentials_listing_idx');
        });

        try {
            DB::statement('ALTER TABLE access_credentials DROP INDEX access_credentials_search_ft');
        } catch (\Exception $e) {
            // Index may not exist
        }
    }
};
