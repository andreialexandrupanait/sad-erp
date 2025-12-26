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
        // Check and add only missing indexes for offer_items
        $existingIndexes = collect(DB::select('SHOW INDEX FROM offer_items'))
            ->pluck('Key_name')
            ->unique()
            ->toArray();

        Schema::table('offer_items', function (Blueprint $table) use ($existingIndexes) {
            // Sort order for ordered retrieval - add only if not exists
            if (!in_array('offer_items_offer_sort_index', $existingIndexes)) {
                $table->index(['offer_id', 'sort_order'], 'offer_items_offer_sort_index');
            }
        });
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
