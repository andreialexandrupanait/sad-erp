<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drops orphaned offers, contracts, and annexes tables.
     * These tables were part of an incomplete feature that was never implemented.
     * The models (Offer.php, Contract.php, Annex.php) have been removed.
     */
    public function up(): void
    {
        // Drop annexes first (has FK to contracts)
        Schema::dropIfExists('annexes');
        // Drop contracts (has FK to offers)
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('offers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate offers table
        Schema::create('offers', function ($table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('offer_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->date('valid_until')->nullable();
            $table->enum('status', ['draft', 'sent', 'approved', 'rejected', 'expired'])->default('draft');
            $table->date('sent_date')->nullable();
            $table->date('approved_date')->nullable();
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'client_id', 'status']);
        });

        // Recreate contracts table
        Schema::create('contracts', function ($table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contract_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->integer('version')->default(1);
            $table->date('signed_date')->nullable();
            $table->string('pdf_path')->nullable();
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'client_id', 'status']);
        });

        // Recreate annexes table
        Schema::create('annexes', function ($table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('annex_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['annex', 'amendment'])->default('annex');
            $table->integer('version')->default(1);
            $table->date('signed_date')->nullable();
            $table->string('pdf_path')->nullable();
            $table->text('changes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'contract_id']);
        });
    }
};
