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
        Schema::create('banking_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Bank account information
            $table->string('bank_name')->default('Banca Transilvania');
            $table->string('account_iban')->unique();
            $table->string('account_name')->nullable();
            $table->string('currency', 3)->default('RON');

            // OAuth2 credentials (encrypted)
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('refresh_token_expires_at')->nullable();

            // PSD2 consent management
            $table->string('consent_id')->nullable();
            $table->timestamp('consent_granted_at')->nullable();
            $table->timestamp('consent_expires_at')->nullable();
            $table->json('consent_scopes')->nullable(); // ['accounts', 'transactions', 'balances']
            $table->enum('consent_status', ['active', 'expired', 'revoked', 'rejected'])->default('active');

            // Sync metadata
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('last_successful_sync_at')->nullable();
            $table->date('sync_from_date')->nullable(); // Track how far back we've synced
            $table->integer('consecutive_failures')->default(0);

            // Status
            $table->enum('status', ['active', 'inactive', 'error'])->default('active');
            $table->text('error_message')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['organization_id', 'status']);
            $table->index('consent_expires_at');
            $table->index('last_sync_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banking_credentials');
    }
};
