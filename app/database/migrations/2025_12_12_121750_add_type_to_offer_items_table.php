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
        Schema::table('offer_items', function (Blueprint $table) {
            // Type: 'custom' (main services list) or 'card' (extra services cards)
            $table->string('type', 20)->default('custom')->after('service_id');
            // Whether the item is selected (for card services, starts as false)
            $table->boolean('is_selected')->default(true)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_items', function (Blueprint $table) {
            $table->dropColumn(['type', 'is_selected']);
        });
    }
};
