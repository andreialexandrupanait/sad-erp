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
        Schema::create('bank_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('banking_credential_id')->constrained()->onDelete('cascade');

            // Sync details
            $table->enum('sync_type', ['manual', 'scheduled', 'historical'])->default('scheduled');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable(); // calculated on completion

            // Date range synced
            $table->date('date_from');
            $table->date('date_to');

            // Results
            $table->enum('status', ['running', 'success', 'partial_success', 'failed'])->default('running');
            $table->integer('transactions_fetched')->default(0);
            $table->integer('transactions_new')->default(0);
            $table->integer('transactions_updated')->default(0);
            $table->integer('transactions_duplicate')->default(0);

            // Matching results
            $table->integer('matches_auto')->default(0);
            $table->integer('matches_manual')->default(0);
            $table->integer('matches_total')->default(0);

            // Error tracking
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable(); // Stack trace, API response, etc.
            $table->integer('api_calls_made')->default(0);
            $table->integer('api_errors')->default(0);

            // Performance metrics
            $table->integer('memory_peak_mb')->nullable();
            $table->json('metadata')->nullable(); // Additional data (rate limits hit, pages fetched, etc.)

            $table->timestamps();

            // Indexes
            $table->index(['banking_credential_id', 'started_at']);
            $table->index(['organization_id', 'status']);
            $table->index('started_at');
            $table->index(['sync_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_sync_logs');
    }
};
