<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create a denormalized table for fast task list display
     * This table contains pre-joined data for rendering task lists
     */
    public function up(): void
    {
        Schema::create('task_display_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('settings_options');

            // Denormalized fields for fast display (no joins needed)
            $table->string('task_name');
            $table->string('list_name')->nullable();
            $table->string('client_name')->nullable();
            $table->string('service_name')->nullable();
            $table->string('assignee_name')->nullable();
            $table->string('priority_label')->nullable();
            $table->string('status_label');
            $table->string('status_color')->nullable();

            // Numeric fields
            $table->integer('time_tracked')->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->integer('position')->default(0);

            // Metadata
            $table->timestamp('updated_at');

            // Optimized indexes for list queries
            $table->index(['organization_id', 'status_id', 'position']); // Main list view
            $table->index(['status_id', 'updated_at']); // Recent tasks per status
            $table->index('due_date'); // Due date sorting
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_display_cache');
    }
};
