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
        Schema::table('tasks', function (Blueprint $table) {
            // Add priority_id column after status_id
            $table->foreignId('priority_id')
                  ->nullable()
                  ->after('status_id')
                  ->constrained('settings_options')
                  ->nullOnDelete();

            // Add parent_task_id for subtasks (self-referencing)
            $table->foreignId('parent_task_id')
                  ->nullable()
                  ->after('service_id')
                  ->constrained('tasks')
                  ->cascadeOnDelete();

            // Add index for better query performance
            $table->index(['parent_task_id', 'list_id']);
            $table->index('priority_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['priority_id']);
            $table->dropForeign(['parent_task_id']);
            $table->dropIndex(['parent_task_id', 'list_id']);
            $table->dropIndex(['priority_id']);
            $table->dropColumn(['priority_id', 'parent_task_id']);
        });
    }
};
