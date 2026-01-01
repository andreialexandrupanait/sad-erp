<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add cascade delete to contracts.offer_id foreign key.
 * This ensures that when an offer is deleted, its associated contracts are also deleted.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Drop existing foreign key
            $table->dropForeign(['offer_id']);

            // Recreate with cascade delete
            $table->foreign('offer_id')
                ->references('id')
                ->on('offers')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Drop cascade foreign key
            $table->dropForeign(['offer_id']);

            // Restore without cascade
            $table->foreign('offer_id')
                ->references('id')
                ->on('offers');
        });
    }
};
