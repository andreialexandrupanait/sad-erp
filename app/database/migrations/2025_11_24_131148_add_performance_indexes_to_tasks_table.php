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
            // Soft deletes performance
            $table->index('deleted_at');

            // Common filter combinations for task queries
            $table->index(['assigned_to', 'status_id', 'due_date']);
            $table->index(['organization_id', 'deleted_at']);

            // Parent-child task queries
            $table->index(['parent_task_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop indexes in reverse order
            $table->dropIndex(['parent_task_id', 'deleted_at']);
            $table->dropIndex(['organization_id', 'deleted_at']);
            $table->dropIndex(['assigned_to', 'status_id', 'due_date']);
            $table->dropIndex(['deleted_at']);
        });
    }
};
