<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Production performance indexes.
     *
     * These indexes address critical performance issues identified in the audit:
     * - financial_revenues.client_id - used in frequent WHERE filters
     * - subscriptions.next_renewal_date - used in scheduled renewal jobs
     * - status columns - used in list filtering across modules
     */
    public function up(): void
    {
        // Financial revenues - client filtering
        if (!$this->indexExists('financial_revenues', 'financial_revenues_client_id_index')) {
            Schema::table('financial_revenues', function (Blueprint $table) {
                $table->index('client_id', 'financial_revenues_client_id_index');
            });
        }

        // Subscriptions - renewal date for scheduled jobs
        if (!$this->indexExists('subscriptions', 'subscriptions_next_renewal_date_index')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->index('next_renewal_date', 'subscriptions_next_renewal_date_index');
            });
        }

        // Subscriptions - status filtering
        if (!$this->indexExists('subscriptions', 'subscriptions_status_index')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->index('status', 'subscriptions_status_index');
            });
        }

        // Domains - status filtering
        if (!$this->indexExists('domains', 'domains_status_index')) {
            Schema::table('domains', function (Blueprint $table) {
                $table->index('status', 'domains_status_index');
            });
        }

        // Clients - status_id filtering (if not already indexed by FK)
        if (!$this->indexExists('clients', 'clients_status_id_index')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->index('status_id', 'clients_status_id_index');
            });
        }

        // Financial expenses - composite index for dashboard queries
        if (!$this->indexExists('financial_expenses', 'financial_expenses_org_year_month_index')) {
            Schema::table('financial_expenses', function (Blueprint $table) {
                $table->index(['organization_id', 'year', 'month'], 'financial_expenses_org_year_month_index');
            });
        }

        // Financial revenues - composite index for dashboard queries
        if (!$this->indexExists('financial_revenues', 'financial_revenues_org_year_month_index')) {
            Schema::table('financial_revenues', function (Blueprint $table) {
                $table->index(['organization_id', 'year', 'month'], 'financial_revenues_org_year_month_index');
            });
        }

        // Offers - status filtering for list views
        if (!$this->indexExists('offers', 'offers_status_index')) {
            Schema::table('offers', function (Blueprint $table) {
                $table->index('status', 'offers_status_index');
            });
        }

        // Contracts - status filtering for list views
        if (!$this->indexExists('contracts', 'contracts_status_index')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->index('status', 'contracts_status_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_revenues', function (Blueprint $table) {
            $table->dropIndex('financial_revenues_client_id_index');
            $table->dropIndex('financial_revenues_org_year_month_index');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('subscriptions_next_renewal_date_index');
            $table->dropIndex('subscriptions_status_index');
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->dropIndex('domains_status_index');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('clients_status_id_index');
        });

        Schema::table('financial_expenses', function (Blueprint $table) {
            $table->dropIndex('financial_expenses_org_year_month_index');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->dropIndex('offers_status_index');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropIndex('contracts_status_index');
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
