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
        // Add timestamps to task_assignees
        Schema::table('task_assignees', function (Blueprint $table) {
            $table->timestamps();
        });

        // Add timestamps to task_watchers
        Schema::table('task_watchers', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_assignees', function (Blueprint $table) {
            $table->dropTimestamps();
        });

        Schema::table('task_watchers', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
