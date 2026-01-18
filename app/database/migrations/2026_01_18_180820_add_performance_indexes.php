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
        $driver = DB::getDriverName();

        // Skip for SQLite as it handles indexes differently during tests
        if ($driver === 'sqlite') {
            return;
        }

        // Offers table indexes
        $this->addIndexSafely('offers', 'created_by_user_id', 'offers_created_by_user_id_index');
        $this->addIndexSafely('offers', ['organization_id', 'status'], 'offers_organization_id_status_index');
        $this->addIndexSafely('offers', ['organization_id', 'created_at'], 'offers_organization_id_created_at_index');
        $this->addIndexSafely('offers', ['client_id', 'status'], 'offers_client_id_status_index');
        $this->addIndexSafely('offers', 'template_id', 'offers_template_id_index');

        // Contracts table indexes
        $this->addIndexSafely('contracts', 'template_id', 'contracts_template_id_index');
        $this->addIndexSafely('contracts', 'status', 'contracts_status_index');
        $this->addIndexSafely('contracts', ['organization_id', 'status'], 'contracts_organization_id_status_index');

        // Clients table indexes
        $this->addIndexSafely('clients', 'created_at', 'clients_created_at_index');

        // Financial revenues table indexes
        $this->addIndexSafely('financial_revenues', ['organization_id', 'year', 'month'], 'financial_revenues_org_year_month_index');
        $this->addIndexSafely('financial_revenues', ['client_id', 'occurred_at'], 'financial_revenues_client_id_occurred_at_index');
        $this->addIndexSafely('financial_revenues', ['organization_id', 'currency', 'year'], 'financial_revenues_org_currency_year_index');

        // Financial expenses table indexes
        $this->addIndexSafely('financial_expenses', ['organization_id', 'year', 'month'], 'financial_expenses_org_year_month_index');
    }

    /**
     * Add an index safely, ignoring if it already exists.
     */
    private function addIndexSafely(string $table, string|array $columns, string $indexName): void
    {
        try {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        } catch (\Exception $e) {
            // Index already exists or other error, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        $this->dropIndexSafely('offers', 'offers_created_by_user_id_index');
        $this->dropIndexSafely('offers', 'offers_template_id_index');
        $this->dropIndexSafely('offers', 'offers_organization_id_status_index');
        $this->dropIndexSafely('offers', 'offers_organization_id_created_at_index');
        $this->dropIndexSafely('offers', 'offers_client_id_status_index');

        $this->dropIndexSafely('contracts', 'contracts_template_id_index');
        $this->dropIndexSafely('contracts', 'contracts_status_index');
        $this->dropIndexSafely('contracts', 'contracts_organization_id_status_index');

        $this->dropIndexSafely('clients', 'clients_created_at_index');

        $this->dropIndexSafely('financial_revenues', 'financial_revenues_org_year_month_index');
        $this->dropIndexSafely('financial_revenues', 'financial_revenues_client_id_occurred_at_index');
        $this->dropIndexSafely('financial_revenues', 'financial_revenues_org_currency_year_index');

        $this->dropIndexSafely('financial_expenses', 'financial_expenses_org_year_month_index');
    }

    /**
     * Drop an index safely, ignoring if it doesn't exist.
     */
    private function dropIndexSafely(string $table, string $indexName): void
    {
        try {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropIndex($indexName);
            });
        } catch (\Exception $e) {
            // Index doesn't exist or other error, ignore
        }
    }
};
