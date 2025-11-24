<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cache table for task counts by status (updated via events)
     * Eliminates need for COUNT queries on page load
     */
    public function up(): void
    {
        Schema::create('task_status_counts_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('settings_options')->cascadeOnDelete();
            $table->foreignId('list_id')->nullable()->constrained('task_lists')->cascadeOnDelete();

            $table->integer('task_count')->default(0);
            $table->timestamp('last_updated');

            // Unique constraint
            $table->unique(['organization_id', 'status_id', 'list_id'], 'org_status_list_unique');

            // Index for quick lookups
            $table->index(['organization_id', 'status_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_status_counts_cache');
    }
};
