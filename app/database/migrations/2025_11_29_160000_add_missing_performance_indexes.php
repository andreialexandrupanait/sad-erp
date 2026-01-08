<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds missing indexes identified during performance audit.
     */
    public function up(): void
    {
        // Settings options - optimize category/status lookups
        if (!$this->indexExists('settings_options', 'settings_options_org_category_active_sort_idx')) {
            Schema::table('settings_options', function (Blueprint $table) {
                $table->index(['organization_id', 'category', 'is_active', 'sort_order'], 'settings_options_org_category_active_sort_idx');
            });
        }

        // Settings options - parent_id for hierarchical queries
        if (!$this->indexExists('settings_options', 'settings_options_parent_id_index')) {
            Schema::table('settings_options', function (Blueprint $table) {
                $table->index('parent_id', 'settings_options_parent_id_index');
            });
        }

        // Financial revenues - year/month for dashboard aggregations
        if (!$this->indexExists('financial_revenues', 'financial_revenues_org_year_month_idx')) {
            Schema::table('financial_revenues', function (Blueprint $table) {
                $table->index(['organization_id', 'year', 'month'], 'financial_revenues_org_year_month_idx');
            });
        }

        // Financial revenues - client_id standalone for client filtering
        if (!$this->indexExists('financial_revenues', 'financial_revenues_client_id_index')) {
            Schema::table('financial_revenues', function (Blueprint $table) {
                $table->index('client_id', 'financial_revenues_client_id_index');
            });
        }

        // Financial expenses - year/month for dashboard aggregations
        if (!$this->indexExists('financial_expenses', 'financial_expenses_org_year_month_idx')) {
            Schema::table('financial_expenses', function (Blueprint $table) {
                $table->index(['organization_id', 'year', 'month'], 'financial_expenses_org_year_month_idx');
            });
        }

        // Domains - status + expiry_date for dashboard widgets
        if (!$this->indexExists('domains', 'domains_org_status_expiry_idx')) {
            Schema::table('domains', function (Blueprint $table) {
                $table->index(['organization_id', 'status', 'expiry_date'], 'domains_org_status_expiry_idx');
            });
        }

        // Subscriptions - status + next_renewal_date for filtering
        if (!$this->indexExists('subscriptions', 'subscriptions_org_status_renewal_idx')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->index(['organization_id', 'status', 'next_renewal_date'], 'subscriptions_org_status_renewal_idx');
            });
        }

        // Clients - organization_id standalone index
        if (!$this->indexExists('clients', 'clients_organization_id_index')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->index('organization_id', 'clients_organization_id_index');
            });
        }

        // Financial files - year/month for file browser
        if (!$this->indexExists('financial_files', 'financial_files_year_month_idx')) {
            Schema::table('financial_files', function (Blueprint $table) {
                $table->index(['an', 'luna', 'tip'], 'financial_files_year_month_idx');
            });
        }

        // Recurring expenses - organization + active status
        if (Schema::hasTable('recurring_expenses') && !$this->indexExists('recurring_expenses', 'recurring_expenses_org_active_idx')) {
            Schema::table('recurring_expenses', function (Blueprint $table) {
                $table->index(['organization_id', 'is_active'], 'recurring_expenses_org_active_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings_options', function (Blueprint $table) {
            $table->dropIndex('settings_options_org_category_active_sort_idx');
            $table->dropIndex('settings_options_parent_id_index');
        });

        Schema::table('financial_revenues', function (Blueprint $table) {
            $table->dropIndex('financial_revenues_org_year_month_idx');
            $table->dropIndex('financial_revenues_client_id_index');
        });

        Schema::table('financial_expenses', function (Blueprint $table) {
            $table->dropIndex('financial_expenses_org_year_month_idx');
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->dropIndex('domains_org_status_expiry_idx');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('subscriptions_org_status_renewal_idx');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('clients_organization_id_index');
        });

        Schema::table('financial_files', function (Blueprint $table) {
            $table->dropIndex('financial_files_year_month_idx');
        });

        if (Schema::hasTable('recurring_expenses')) {
            Schema::table('recurring_expenses', function (Blueprint $table) {
                $table->dropIndex('recurring_expenses_org_active_idx');
            });
        }
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
