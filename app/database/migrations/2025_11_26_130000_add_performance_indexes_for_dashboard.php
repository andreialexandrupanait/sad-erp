<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * These indexes optimize the dashboard and import queries identified as slow.
     */
    public function up(): void
    {
        // Financial revenues - optimize dashboard queries
        Schema::table('financial_revenues', function (Blueprint $table) {
            // Composite index for year/month/currency queries (dashboard trends)
            $table->index(['year', 'month', 'currency'], 'idx_revenues_year_month_currency');

            // Index for client-based revenue queries
            $table->index(['client_id', 'currency'], 'idx_revenues_client_currency');
        });

        // Financial expenses - optimize dashboard queries
        Schema::table('financial_expenses', function (Blueprint $table) {
            // Composite index for year/month/currency queries (dashboard trends)
            $table->index(['year', 'month', 'currency'], 'idx_expenses_year_month_currency');

            // Index for category breakdown queries
            $table->index(['year', 'category_option_id'], 'idx_expenses_year_category');
        });

        // Domains - optimize expiry date queries
        Schema::table('domains', function (Blueprint $table) {
            // Index for expiry date range queries (dashboard renewals)
            $table->index('expiry_date', 'idx_domains_expiry_date');

            // Composite for status + expiry queries
            $table->index(['status', 'expiry_date'], 'idx_domains_status_expiry');
        });

        // Subscriptions - optimize status queries
        Schema::table('subscriptions', function (Blueprint $table) {
            // Composite for status + billing cycle + next renewal queries
            $table->index(['status', 'billing_cycle'], 'idx_subscriptions_status_billing');
            $table->index(['status', 'next_renewal_date'], 'idx_subscriptions_status_renewal');
        });

        // Clients - optimize created_at queries for growth metrics
        Schema::table('clients', function (Blueprint $table) {
            $table->index('created_at', 'idx_clients_created_at');
            $table->index('updated_at', 'idx_clients_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_revenues', function (Blueprint $table) {
            $table->dropIndex('idx_revenues_year_month_currency');
            $table->dropIndex('idx_revenues_client_currency');
        });

        Schema::table('financial_expenses', function (Blueprint $table) {
            $table->dropIndex('idx_expenses_year_month_currency');
            $table->dropIndex('idx_expenses_year_category');
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->dropIndex('idx_domains_expiry_date');
            $table->dropIndex('idx_domains_status_expiry');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_subscriptions_status_billing');
            $table->dropIndex('idx_subscriptions_status_renewal');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('idx_clients_created_at');
            $table->dropIndex('idx_clients_updated_at');
        });
    }
};
