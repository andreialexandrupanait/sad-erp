<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enhance contract_items table and add finalization fields to contracts.
 *
 * Changes:
 * 1. Add 'title' column to contract_items for service name
 * 2. Add finalization fields to contracts (is_finalized, finalized_at)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enhance contract_items table
        Schema::table('contract_items', function (Blueprint $table) {
            // Add title column for service name (after service_id)
            if (!Schema::hasColumn('contract_items', 'title')) {
                $table->string('title')->nullable()->after('service_id');
            }
        });

        // Add finalization fields to contracts
        Schema::table('contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('contracts', 'is_finalized')) {
                $table->boolean('is_finalized')->default(false)->after('status');
            }
            if (!Schema::hasColumn('contracts', 'finalized_at')) {
                $table->timestamp('finalized_at')->nullable()->after('is_finalized');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_items', function (Blueprint $table) {
            if (Schema::hasColumn('contract_items', 'title')) {
                $table->dropColumn('title');
            }
        });

        Schema::table('contracts', function (Blueprint $table) {
            if (Schema::hasColumn('contracts', 'is_finalized')) {
                $table->dropColumn('is_finalized');
            }
            if (Schema::hasColumn('contracts', 'finalized_at')) {
                $table->dropColumn('finalized_at');
            }
        });
    }
};
