<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drop unused duplicate financial tables:
     * - revenues (replaced by financial_revenues)
     * - expenses (replaced by financial_expenses)
     */
    public function up(): void
    {
        // Drop revenues table (replaced by financial_revenues)
        Schema::dropIfExists('revenues');

        // Drop expenses table (replaced by financial_expenses)
        Schema::dropIfExists('expenses');
    }

    /**
     * Reverse the migrations.
     *
     * Recreate the tables in case rollback is needed
     */
    public function down(): void
    {
        // Recreate revenues table
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('RON');
            $table->date('revenue_date');
            $table->string('source')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('invoice_number')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'client_id', 'revenue_date']);
        });

        // Recreate expenses table
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('RON');
            $table->date('expense_date');
            $table->string('category')->nullable();
            $table->string('vendor')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('receipt_path')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'expense_date', 'category']);
        });
    }
};
