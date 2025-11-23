<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the infrastructure for custom fields in tasks:
     * - task_custom_fields: field definitions (text, number, date, dropdown, etc.)
     * - task_custom_field_values: actual values for each task
     */
    public function up(): void
    {
        // Custom field definitions
        Schema::create('task_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Field name (e.g., "Client Budget", "Deadline Type")
            $table->string('slug')->index(); // URL-friendly name
            $table->enum('type', ['text', 'number', 'date', 'dropdown', 'checkbox', 'email', 'url', 'phone'])->default('text');
            $table->json('options')->nullable(); // For dropdown/checkbox (array of options)
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
            $table->unique(['organization_id', 'slug']);
        });

        // Custom field values for each task
        Schema::create('task_custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_field_id')->constrained('task_custom_fields')->cascadeOnDelete();
            $table->text('value')->nullable(); // Stores the actual value (JSON for complex types)
            $table->timestamps();

            $table->index(['task_id', 'custom_field_id']);
            $table->unique(['task_id', 'custom_field_id']); // One value per field per task
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_custom_field_values');
        Schema::dropIfExists('task_custom_fields');
    }
};
