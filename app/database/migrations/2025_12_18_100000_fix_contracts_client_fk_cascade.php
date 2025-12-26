<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix the contracts.client_id foreign key constraint.
 *
 * Previously: cascadeOnDelete() - deleting a client would delete all their contracts
 * Now: nullOnDelete() - deleting a client sets contract.client_id to NULL
 *
 * This is safer for legal documents as contracts should not be automatically
 * deleted when a client is removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['client_id']);

            // Re-add the foreign key with nullOnDelete instead of cascadeOnDelete
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['client_id']);

            // Restore the original cascadeOnDelete behavior
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->cascadeOnDelete();
        });
    }
};
