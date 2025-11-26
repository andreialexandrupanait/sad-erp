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
        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Expense template data
            $table->string('name'); // Template name for identification
            $table->string('document_name_template'); // Can include {month}, {year} placeholders
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('RON');
            $table->foreignId('category_option_id')->nullable()->constrained('settings_options')->nullOnDelete();
            $table->text('note')->nullable();

            // Recurrence settings
            $table->enum('frequency', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->unsignedTinyInteger('day_of_month')->default(1); // 1-28 (safe for all months)
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null = no end date

            // Tracking
            $table->date('last_generated_at')->nullable();
            $table->date('next_due_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_create')->default(false); // Auto-create or just reminder

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['organization_id', 'user_id', 'is_active']);
            $table->index(['next_due_date', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_expenses');
    }
};
