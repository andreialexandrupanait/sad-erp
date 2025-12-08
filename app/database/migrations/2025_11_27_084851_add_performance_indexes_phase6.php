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
     * Phase 6 Performance Indexes:
     * - financial_revenues.organization_id - For revenue filtering
     * - financial_revenues year/month - For dashboard queries
     * - financial_expenses year/month - For dashboard queries
     */
    public function up(): void
    {
        // Index for financial_revenues - organization filtering
        if (Schema::hasColumn('financial_revenues', 'organization_id') && !$this->indexExists('financial_revenues', 'idx_revenues_org_id')) {
            Schema::table('financial_revenues', function (Blueprint $table) {
                $table->index('organization_id', 'idx_revenues_org_id');
            });
        }

        // Composite index for financial_revenues - year/month filtering (common dashboard query)
        if (!$this->indexExists('financial_revenues', 'idx_revenues_year_month')) {
            Schema::table('financial_revenues', function (Blueprint $table) {
                $table->index(['year', 'month'], 'idx_revenues_year_month');
            });
        }

        // Composite index for financial_expenses - year/month filtering
        if (Schema::hasTable('financial_expenses') && !$this->indexExists('financial_expenses', 'idx_expenses_year_month')) {
            Schema::table('financial_expenses', function (Blueprint $table) {
                $table->index(['year', 'month'], 'idx_expenses_year_month');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->indexExists('financial_revenues', 'idx_revenues_org_id')) {
            Schema::table('financial_revenues', function (Blueprint $table) {
                $table->dropIndex('idx_revenues_org_id');
            });
        }

        if ($this->indexExists('financial_revenues', 'idx_revenues_year_month')) {
            Schema::table('financial_revenues', function (Blueprint $table) {
                $table->dropIndex('idx_revenues_year_month');
            });
        }

        if ($this->indexExists('financial_expenses', 'idx_expenses_year_month')) {
            Schema::table('financial_expenses', function (Blueprint $table) {
                $table->dropIndex('idx_expenses_year_month');
            });
        }
    }

    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
