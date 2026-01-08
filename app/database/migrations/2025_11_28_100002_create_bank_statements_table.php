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
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('banking_credential_id')->constrained()->onDelete('cascade');

            // Statement period
            $table->integer('year'); // 2024
            $table->integer('month'); // 1-12
            $table->date('period_start');
            $table->date('period_end');

            // PDF file information
            $table->string('file_path')->nullable(); // storage/app/banking/extrase/2024/01.pdf
            $table->string('file_name')->nullable(); // 01.pdf or Jan-2024.pdf
            $table->bigInteger('file_size')->nullable(); // bytes
            $table->string('file_hash')->nullable(); // SHA-256 hash for integrity

            // Download status
            $table->enum('download_status', ['pending', 'downloading', 'completed', 'failed', 'unavailable'])->default('pending');
            $table->integer('download_attempts')->default(0);
            $table->timestamp('last_download_attempt_at')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->text('download_error')->nullable();

            // Statement metadata
            $table->decimal('opening_balance', 15, 2)->nullable();
            $table->decimal('closing_balance', 15, 2)->nullable();
            $table->integer('transaction_count')->nullable();
            $table->string('currency', 3)->default('RON');

            // Manual upload support
            $table->boolean('manually_uploaded')->default(false);
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Unique constraint: one statement per account per month
            $table->unique(['banking_credential_id', 'year', 'month']);

            // Indexes
            $table->index(['organization_id', 'year', 'month']);
            $table->index(['banking_credential_id', 'download_status']);
            $table->index('downloaded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_statements');
    }
};
