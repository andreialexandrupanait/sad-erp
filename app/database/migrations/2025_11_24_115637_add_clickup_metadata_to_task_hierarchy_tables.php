<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add clickup_metadata to task_spaces
        Schema::table('task_spaces', function (Blueprint $table) {
            $table->json('clickup_metadata')->nullable()->after('position');
        });

        // Add clickup_metadata to task_folders
        Schema::table('task_folders', function (Blueprint $table) {
            $table->json('clickup_metadata')->nullable()->after('position');
        });

        // Add clickup_metadata to task_lists
        Schema::table('task_lists', function (Blueprint $table) {
            $table->json('clickup_metadata')->nullable()->after('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_spaces', function (Blueprint $table) {
            $table->dropColumn('clickup_metadata');
        });

        Schema::table('task_folders', function (Blueprint $table) {
            $table->dropColumn('clickup_metadata');
        });

        Schema::table('task_lists', function (Blueprint $table) {
            $table->dropColumn('clickup_metadata');
        });
    }
};
