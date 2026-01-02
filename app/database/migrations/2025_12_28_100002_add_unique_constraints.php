<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Check if an index exists (database-agnostic).
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = $connection->select(
                "SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = ? AND name = ?",
                [$table, $indexName]
            );
            return count($indexes) > 0;
        }

        $indexes = $connection->select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Unique constraints
        $uniqueConstraints = [
            ['table' => 'domains', 'columns' => ['domain_name', 'organization_id'], 'name' => 'idx_unique_domain_per_org'],
            ['table' => 'exchange_rates', 'columns' => ['from_currency', 'to_currency', 'rate_date'], 'name' => 'idx_unique_exchange_rate'],
            ['table' => 'internal_accounts', 'columns' => ['account_name', 'organization_id'], 'name' => 'idx_unique_internal_account'],
        ];

        foreach ($uniqueConstraints as $constraint) {
            if (!Schema::hasTable($constraint['table'])) {
                continue;
            }

            if (!$this->indexExists($constraint['table'], $constraint['name'])) {
                try {
                    Schema::table($constraint['table'], function (Blueprint $table) use ($constraint) {
                        $table->unique($constraint['columns'], $constraint['name']);
                    });
                } catch (\Exception $e) {
                    // Skip if constraint violates existing data
                }
            }
        }

        // Regular composite indexes
        $indexes = [
            ['table' => 'subscriptions', 'columns' => ['organization_id', 'vendor_name'], 'name' => 'idx_subscriptions_org_vendor'],
            ['table' => 'clients', 'columns' => ['organization_id', 'email'], 'name' => 'idx_clients_org_email'],
            ['table' => 'access_credentials', 'columns' => ['organization_id', 'platform'], 'name' => 'idx_credentials_org_platform'],
        ];

        foreach ($indexes as $index) {
            if (!Schema::hasTable($index['table'])) {
                continue;
            }

            if (!$this->indexExists($index['table'], $index['name'])) {
                try {
                    Schema::table($index['table'], function (Blueprint $table) use ($index) {
                        $table->index($index['columns'], $index['name']);
                    });
                } catch (\Exception $e) {
                    // Index might already exist
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = [
            ['table' => 'domains', 'name' => 'idx_unique_domain_per_org', 'unique' => true],
            ['table' => 'exchange_rates', 'name' => 'idx_unique_exchange_rate', 'unique' => true],
            ['table' => 'internal_accounts', 'name' => 'idx_unique_internal_account', 'unique' => true],
            ['table' => 'subscriptions', 'name' => 'idx_subscriptions_org_vendor', 'unique' => false],
            ['table' => 'clients', 'name' => 'idx_clients_org_email', 'unique' => false],
            ['table' => 'access_credentials', 'name' => 'idx_credentials_org_platform', 'unique' => false],
        ];

        foreach ($indexes as $index) {
            if (!Schema::hasTable($index['table'])) {
                continue;
            }

            if ($this->indexExists($index['table'], $index['name'])) {
                try {
                    Schema::table($index['table'], function (Blueprint $table) use ($index) {
                        if ($index['unique']) {
                            $table->dropUnique($index['name']);
                        } else {
                            $table->dropIndex($index['name']);
                        }
                    });
                } catch (\Exception $e) {
                    // Ignore
                }
            }
        }
    }
};
