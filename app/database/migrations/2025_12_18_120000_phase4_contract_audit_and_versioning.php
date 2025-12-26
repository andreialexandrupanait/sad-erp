<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 4: Audit trail and versioning for Contracts module.
 *
 * Creates:
 * 1. contract_activities - Audit trail for contract actions
 * 2. contract_versions - Content versioning for contracts
 * 3. Adds editing_lock fields to contracts for concurrent edit prevention
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create contract_activities table for audit trail
        Schema::create('contract_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('action'); // created, updated, activated, terminated, etc.
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable(); // Additional context (old values, reason, etc.)
            $table->json('changes')->nullable(); // Field changes for updates

            $table->timestamp('created_at');

            $table->index(['contract_id', 'action']);
            $table->index(['contract_id', 'created_at']);
        });

        // 2. Create contract_versions table for content versioning
        Schema::create('contract_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedInteger('version_number');
            $table->longText('content'); // Snapshot of contract content
            $table->json('blocks')->nullable(); // Snapshot of blocks if using block editor
            $table->string('reason')->nullable(); // Optional description of changes
            $table->string('content_hash', 32); // MD5 hash to detect duplicates

            $table->timestamps();

            $table->unique(['contract_id', 'version_number']);
            $table->index(['contract_id', 'created_at']);
        });

        // 3. Add editing lock fields to contracts
        Schema::table('contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('contracts', 'locked_by')) {
                $table->foreignId('locked_by')->nullable()->after('is_finalized')
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('contracts', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('locked_by');
            }
            if (!Schema::hasColumn('contracts', 'current_version')) {
                $table->unsignedInteger('current_version')->default(1)->after('locked_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove lock fields from contracts
        Schema::table('contracts', function (Blueprint $table) {
            if (Schema::hasColumn('contracts', 'locked_by')) {
                $table->dropForeign(['locked_by']);
                $table->dropColumn('locked_by');
            }
            if (Schema::hasColumn('contracts', 'locked_at')) {
                $table->dropColumn('locked_at');
            }
            if (Schema::hasColumn('contracts', 'current_version')) {
                $table->dropColumn('current_version');
            }
        });

        Schema::dropIfExists('contract_versions');
        Schema::dropIfExists('contract_activities');
    }
};
