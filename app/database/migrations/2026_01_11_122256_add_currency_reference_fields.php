<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add EUR reference fields to financial tables.
 *
 * Purpose: Store original EUR amounts as reference while keeping RON as primary.
 * - amount/price = always RON (primary accounting value)
 * - amount_eur/price_eur = optional EUR reference (for display)
 * - exchange_rate = rate used for conversion (audit trail)
 *
 * Safety: Only adds nullable columns. No existing data is modified.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Add EUR reference fields to financial_revenues
        Schema::table('financial_revenues', function (Blueprint $table) {
            $table->decimal('amount_eur', 15, 2)->nullable()->after('amount')
                ->comment('Original EUR amount (reference only)');
            $table->decimal('exchange_rate', 10, 6)->nullable()->after('currency')
                ->comment('Exchange rate used for conversion');
        });

        // Add EUR reference fields to financial_expenses
        Schema::table('financial_expenses', function (Blueprint $table) {
            $table->decimal('amount_eur', 15, 2)->nullable()->after('amount')
                ->comment('Original EUR amount (reference only)');
            $table->decimal('exchange_rate', 10, 6)->nullable()->after('currency')
                ->comment('Exchange rate used for conversion');
        });

        // Add EUR reference fields to subscriptions
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('price_eur', 10, 2)->nullable()->after('price')
                ->comment('Original EUR price (reference only)');
            $table->decimal('exchange_rate', 10, 6)->nullable()->after('currency')
                ->comment('Exchange rate used for conversion');
        });
    }

    public function down(): void
    {
        Schema::table('financial_revenues', function (Blueprint $table) {
            $table->dropColumn(['amount_eur', 'exchange_rate']);
        });

        Schema::table('financial_expenses', function (Blueprint $table) {
            $table->dropColumn(['amount_eur', 'exchange_rate']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['price_eur', 'exchange_rate']);
        });
    }
};
