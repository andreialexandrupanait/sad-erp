<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add performance indexes to financial tables.
 *
 * This migration adds missing indexes identified in the Financial Module audit:
 * - client_id index for revenue queries
 * - Composite indexes for year/month/currency aggregations
 * - organization_id composite indexes for scoped queries
 *
 * SAFE: Adding indexes does not modify data, only improves query performance.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to financial_revenues table
        Schema::table('financial_revenues', function (Blueprint $table) {
            // Index for client-based queries (RevenueController, DashboardController)
            if (!$this->indexExists('financial_revenues', 'financial_revenues_client_id_index')) {
                $table->index('client_id', 'financial_revenues_client_id_index');
            }

            // Composite index for dashboard aggregations (year + month + currency)
            if (!$this->indexExists('financial_revenues', 'financial_revenues_year_month_currency_index')) {
                $table->index(['year', 'month', 'currency'], 'financial_revenues_year_month_currency_index');
            }

            // Composite index for organization-scoped year queries
            if (!$this->indexExists('financial_revenues', 'financial_revenues_org_year_month_index')) {
                $table->index(['organization_id', 'year', 'month'], 'financial_revenues_org_year_month_index');
            }
        });

        // Add indexes to financial_expenses table
        Schema::table('financial_expenses', function (Blueprint $table) {
            // Composite index for dashboard aggregations (year + month + currency)
            if (!$this->indexExists('financial_expenses', 'financial_expenses_year_month_currency_index')) {
                $table->index(['year', 'month', 'currency'], 'financial_expenses_year_month_currency_index');
            }

            // Composite index for organization-scoped year queries
            if (!$this->indexExists('financial_expenses', 'financial_expenses_org_year_month_index')) {
                $table->index(['organization_id', 'year', 'month'], 'financial_expenses_org_year_month_index');
            }

            // Index for category-based filtering
            if (!$this->indexExists('financial_expenses', 'financial_expenses_category_option_id_index')) {
                $table->index('category_option_id', 'financial_expenses_category_option_id_index');
            }
        });

        // Add indexes to financial_files table
        Schema::table('financial_files', function (Blueprint $table) {
            // Composite index for polymorphic relationship queries
            if (!$this->indexExists('financial_files', 'financial_files_entity_type_entity_id_index')) {
                $table->index(['entity_type', 'entity_id'], 'financial_files_entity_type_entity_id_index');
            }

            // Composite index for file browser (year/month/type filtering)
            if (!$this->indexExists('financial_files', 'financial_files_an_luna_tip_index')) {
                $table->index(['an', 'luna', 'tip'], 'financial_files_an_luna_tip_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_revenues', function (Blueprint $table) {
            $table->dropIndex('financial_revenues_client_id_index');
            $table->dropIndex('financial_revenues_year_month_currency_index');
            $table->dropIndex('financial_revenues_org_year_month_index');
        });

        Schema::table('financial_expenses', function (Blueprint $table) {
            $table->dropIndex('financial_expenses_year_month_currency_index');
            $table->dropIndex('financial_expenses_org_year_month_index');
            $table->dropIndex('financial_expenses_category_option_id_index');
        });

        Schema::table('financial_files', function (Blueprint $table) {
            $table->dropIndex('financial_files_entity_type_entity_id_index');
            $table->dropIndex('financial_files_an_luna_tip_index');
        });
    }

    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if ($index['name'] === $indexName) {
                return true;
            }
        }
        return false;
    }
};
