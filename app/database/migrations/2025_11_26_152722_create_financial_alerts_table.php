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
        Schema::create('financial_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Alert configuration
            $table->string('name');
            $table->enum('type', ['expense_budget', 'category_budget', 'revenue_target', 'profit_margin'])->default('expense_budget');
            $table->foreignId('category_option_id')->nullable()->constrained('settings_options')->nullOnDelete();
            $table->decimal('threshold', 12, 2);
            $table->string('currency', 3)->default('RON');
            $table->enum('period', ['monthly', 'quarterly', 'yearly'])->default('monthly');

            // Alert state
            $table->boolean('is_active')->default(true);
            $table->boolean('email_notification')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->decimal('last_triggered_value', 12, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['organization_id', 'user_id', 'is_active']);
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_alerts');
    }
};
