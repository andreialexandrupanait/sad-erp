<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add USD reference field to subscriptions table.
 *
 * Purpose: Store original USD amounts as reference while keeping RON as primary.
 * - price = always RON (primary accounting value)
 * - price_usd = optional USD reference (for display)
 *
 * Safety: Only adds nullable column. No existing data is modified.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('price_usd', 10, 2)->nullable()->after('price_eur')
                ->comment('Original USD price (reference only)');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('price_usd');
        });
    }
};
