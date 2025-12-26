<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds project_name and website fields to support multiple projects per client.
     * - project_name: Custom name for the credential (instead of auto-generated)
     * - website: Direct link to the project/website for quick access
     */
    public function up(): void
    {
        Schema::table('access_credentials', function (Blueprint $table) {
            // Custom name for the credential (user-defined, not auto-generated)
            $table->string('project_name')->nullable()->after('client_id');

            // Website/project URL - separate from login URL
            $table->string('website')->nullable()->after('url');

            // Index for searching by project name
            $table->index('project_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_credentials', function (Blueprint $table) {
            $table->dropIndex(['project_name']);
            $table->dropColumn(['project_name', 'website']);
        });
    }
};
