<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add unique constraints to prevent duplicate data and ensure data integrity.
     * This also improves query performance for lookups.
     */
    public function up(): void
    {
        // Unique constraints
        $uniqueConstraints = [
            ['table' => 'domains', 'columns' => 'domain_name, organization_id', 'name' => 'idx_unique_domain_per_org'],
            ['table' => 'exchange_rates', 'columns' => 'from_currency, to_currency, rate_date', 'name' => 'idx_unique_exchange_rate'],
            ['table' => 'internal_accounts', 'columns' => 'account_name, organization_id', 'name' => 'idx_unique_internal_account'],
        ];

        foreach ($uniqueConstraints as $constraint) {
            // Check if table exists
            $tableExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
                AND table_name = ?
            ", [$constraint['table']]);

            if ($tableExists[0]->count == 0) {
                continue; // Skip if table doesn't exist
            }

            $exists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = ?
                AND index_name = ?
            ", [$constraint['table'], $constraint['name']]);

            if ($exists[0]->count == 0) {
                try {
                    DB::statement("ALTER TABLE `{$constraint['table']}` ADD UNIQUE INDEX `{$constraint['name']}` ({$constraint['columns']})");
                } catch (\Exception $e) {
                    // Skip if constraint violates existing data
                    echo "Skipping unique constraint {$constraint['name']} due to existing duplicates\n";
                }
            }
        }

        // Regular composite indexes
        $indexes = [
            ['table' => 'subscriptions', 'columns' => 'organization_id, vendor_name', 'name' => 'idx_subscriptions_org_vendor'],
            ['table' => 'clients', 'columns' => 'organization_id, email', 'name' => 'idx_clients_org_email'],
            ['table' => 'access_credentials', 'columns' => 'organization_id, platform', 'name' => 'idx_credentials_org_platform'],
        ];

        foreach ($indexes as $index) {
            // Check if table exists
            $tableExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
                AND table_name = ?
            ", [$index['table']]);

            if ($tableExists[0]->count == 0) {
                continue; // Skip if table doesn't exist
            }

            $exists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = ?
                AND index_name = ?
            ", [$index['table'], $index['name']]);

            if ($exists[0]->count == 0) {
                DB::statement("ALTER TABLE `{$index['table']}` ADD INDEX `{$index['name']}` ({$index['columns']})");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexNames = [
            'idx_unique_domain_per_org',
            'idx_unique_exchange_rate',
            'idx_unique_internal_account',
            'idx_subscriptions_org_vendor',
            'idx_clients_org_email',
            'idx_credentials_org_platform',
        ];

        $tables = [
            'domains', 'exchange_rates', 'internal_accounts',
            'subscriptions', 'clients', 'access_credentials'
        ];

        foreach ($tables as $table) {
            foreach ($indexNames as $indexName) {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX IF EXISTS `{$indexName}`");
            }
        }
    }
};
