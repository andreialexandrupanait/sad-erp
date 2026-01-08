<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Performance indexes for the Bidding/Offer module.
     * Only adds indexes that don't already exist.
     */
    public function up(): void
    {
        // Check if table exists first
        if (!Schema::hasTable('offer_items')) {
            return;
        }

        // Try to create index, silently skip if it already exists
        try {
            Schema::table('offer_items', function (Blueprint $table) {
                $table->index(['offer_id', 'sort_order'], 'offer_items_offer_sort_index');
            });
        } catch (\Exception $e) {
            // Index already exists or other issue - skip silently
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_items', function (Blueprint $table) {
            $table->dropIndex('offer_items_offer_sort_index');
        });
    }
};
