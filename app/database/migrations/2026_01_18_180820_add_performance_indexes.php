<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Helper to check if index exists
        $indexExists = function (string $table, string $indexName): bool {
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        };

        // Offers table indexes
        Schema::table('offers', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('offers', 'offers_created_by_user_id_index')) {
                $table->index('created_by_user_id', 'offers_created_by_user_id_index');
            }
            if (!$indexExists('offers', 'offers_template_id_index')) {
                $table->index('template_id', 'offers_template_id_index');
            }
            if (!$indexExists('offers', 'offers_organization_id_status_index')) {
                $table->index(['organization_id', 'status'], 'offers_organization_id_status_index');
            }
            if (!$indexExists('offers', 'offers_organization_id_created_at_index')) {
                $table->index(['organization_id', 'created_at'], 'offers_organization_id_created_at_index');
            }
            if (!$indexExists('offers', 'offers_client_id_status_index')) {
                $table->index(['client_id', 'status'], 'offers_client_id_status_index');
            }
        });

        // Contracts table indexes
        Schema::table('contracts', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('contracts', 'contracts_template_id_index')) {
                $table->index('template_id', 'contracts_template_id_index');
            }
            if (!$indexExists('contracts', 'contracts_status_index')) {
                $table->index('status', 'contracts_status_index');
            }
            if (!$indexExists('contracts', 'contracts_organization_id_status_index')) {
                $table->index(['organization_id', 'status'], 'contracts_organization_id_status_index');
            }
        });

        // Clients table indexes
        Schema::table('clients', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('clients', 'clients_created_at_index')) {
                $table->index('created_at', 'clients_created_at_index');
            }
        });

        // Financial revenues table indexes
        Schema::table('financial_revenues', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('financial_revenues', 'financial_revenues_org_year_month_index')) {
                $table->index(['organization_id', 'year', 'month'], 'financial_revenues_org_year_month_index');
            }
            if (!$indexExists('financial_revenues', 'financial_revenues_client_id_occurred_at_index')) {
                $table->index(['client_id', 'occurred_at'], 'financial_revenues_client_id_occurred_at_index');
            }
            if (!$indexExists('financial_revenues', 'financial_revenues_org_currency_year_index')) {
                $table->index(['organization_id', 'currency', 'year'], 'financial_revenues_org_currency_year_index');
            }
        });

        // Financial expenses table indexes
        Schema::table('financial_expenses', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('financial_expenses', 'financial_expenses_org_year_month_index')) {
                $table->index(['organization_id', 'year', 'month'], 'financial_expenses_org_year_month_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropIndex('offers_created_by_user_id_index');
            $table->dropIndex('offers_template_id_index');
            $table->dropIndex('offers_organization_id_status_index');
            $table->dropIndex('offers_organization_id_created_at_index');
            $table->dropIndex('offers_client_id_status_index');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropIndex('contracts_template_id_index');
            $table->dropIndex('contracts_status_index');
            $table->dropIndex('contracts_organization_id_status_index');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('clients_created_at_index');
        });

        Schema::table('financial_revenues', function (Blueprint $table) {
            $table->dropIndex('financial_revenues_org_year_month_index');
            $table->dropIndex('financial_revenues_client_id_occurred_at_index');
            $table->dropIndex('financial_revenues_org_currency_year_index');
        });

        Schema::table('financial_expenses', function (Blueprint $table) {
            $table->dropIndex('financial_expenses_org_year_month_index');
        });
    }
};
