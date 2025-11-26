<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds composite index for the most common query pattern in ClickUp list view:
     * WHERE list_id = X AND status_id = Y ORDER BY position
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Composite index for list view queries (grouped by status, ordered by position)
            $table->index(['list_id', 'status_id', 'position'], 'tasks_list_status_position_index');

            // Composite index for organization-wide queries with status filter
            $table->index(['organization_id', 'status_id', 'position'], 'tasks_org_status_position_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_list_status_position_index');
            $table->dropIndex('tasks_org_status_position_index');
        });
    }
};
