<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('access_credentials', function (Blueprint $table) {
            // Add credential_type field for categorization
            $table->string('credential_type', 50)->default('other')->after('platform');

            // Add site_name column (will migrate data from project_name)
            $table->string('site_name')->nullable()->after('client_id');

            // Add indexes for filtering and grouping
            $table->index('credential_type');
            $table->index('site_name');
        });

        // Migrate data from project_name to site_name
        DB::statement('UPDATE access_credentials SET site_name = project_name WHERE project_name IS NOT NULL');

        // Drop project_name column and its index
        Schema::table('access_credentials', function (Blueprint $table) {
            // Check if index exists before dropping
            $indexExists = collect(DB::select("SHOW INDEX FROM access_credentials WHERE Key_name = 'access_credentials_project_name_index'"))->isNotEmpty();
            if ($indexExists) {
                $table->dropIndex(['project_name']);
            }
            $table->dropColumn('project_name');
        });
    }

    public function down(): void
    {
        Schema::table('access_credentials', function (Blueprint $table) {
            // Re-add project_name
            $table->string('project_name')->nullable()->after('client_id');
            $table->index('project_name');
        });

        // Migrate data back
        DB::statement('UPDATE access_credentials SET project_name = site_name WHERE site_name IS NOT NULL');

        Schema::table('access_credentials', function (Blueprint $table) {
            $table->dropIndex(['credential_type']);
            $table->dropIndex(['site_name']);
            $table->dropColumn(['credential_type', 'site_name']);
        });
    }
};
