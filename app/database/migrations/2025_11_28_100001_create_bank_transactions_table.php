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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('banking_credential_id')->constrained()->onDelete('cascade');

            // Transaction identification
            $table->string('transaction_id')->unique(); // BT's transaction ID
            $table->string('entry_reference')->nullable(); // Additional reference
            $table->date('booking_date'); // Date when transaction was posted
            $table->date('value_date'); // Date when amount is actually credited/debited

            // Transaction details
            $table->enum('type', ['incoming', 'outgoing']);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('RON');
            $table->text('description')->nullable();
            $table->string('debtor_name')->nullable(); // For incoming: who sent money
            $table->string('debtor_account')->nullable();
            $table->string('creditor_name')->nullable(); // For outgoing: who received money
            $table->string('creditor_account')->nullable();
            $table->string('remittance_information')->nullable(); // Payment reference

            // Matching status
            $table->enum('match_status', ['unmatched', 'auto_matched', 'manual_matched', 'ignored'])->default('unmatched');
            $table->decimal('match_confidence', 5, 2)->nullable(); // 0.00 - 100.00
            $table->foreignId('matched_revenue_id')->nullable()->constrained('financial_revenues')->nullOnDelete();
            $table->foreignId('matched_expense_id')->nullable()->constrained('financial_expenses')->nullOnDelete();
            $table->timestamp('matched_at')->nullable();
            $table->foreignId('matched_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('match_notes')->nullable();

            // Raw data from API
            $table->json('raw_data')->nullable(); // Store complete API response

            // Status
            $table->enum('status', ['pending', 'processed', 'reconciled', 'disputed'])->default('pending');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['organization_id', 'type', 'booking_date']);
            $table->index(['organization_id', 'match_status']);
            $table->index(['banking_credential_id', 'booking_date']);
            $table->index('transaction_id');
            $table->index(['amount', 'booking_date']); // For matching algorithm
            $table->index('matched_revenue_id');
            $table->index('matched_expense_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
