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
            $table->string('original_currency', 3)->nullable()->after('unit_price');
            $table->decimal('original_unit_price', 12, 2)->nullable()->after('original_currency');
            $table->decimal('exchange_rate', 10, 6)->nullable()->after('original_unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_items', function (Blueprint $table) {
            $table->dropColumn(['original_currency', 'original_unit_price', 'exchange_rate']);
        });
    }
};
