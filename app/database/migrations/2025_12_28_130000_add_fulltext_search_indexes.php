<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add FULLTEXT indexes for search optimization.
     * Note: FULLTEXT is MySQL-specific and will be skipped on SQLite.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // FULLTEXT indexes are MySQL-specific, skip on SQLite
        if ($driver === 'sqlite') {
            return;
        }

        // Check MySQL version - FULLTEXT requires MySQL 5.6+ with InnoDB
        try {
            $version = DB::select("SELECT VERSION() as version")[0]->version;
            if (version_compare($version, '5.6', '<')) {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        $fulltextIndexes = [
            ['table' => 'clients', 'columns' => ['name', 'company_name', 'email'], 'name' => 'clients_fulltext_search'],
            ['table' => 'offers', 'columns' => ['title', 'description'], 'name' => 'offers_fulltext_search'],
            ['table' => 'contracts', 'columns' => ['title', 'description'], 'name' => 'contracts_fulltext_search'],
            ['table' => 'access_credentials', 'columns' => ['site_name', 'platform', 'username', 'url'], 'name' => 'credentials_fulltext_search'],
        ];

        foreach ($fulltextIndexes as $index) {
            if (!Schema::hasTable($index['table'])) {
                continue;
            }

            // Check if all columns exist
            $allColumnsExist = true;
            foreach ($index['columns'] as $column) {
                if (!Schema::hasColumn($index['table'], $column)) {
                    $allColumnsExist = false;
                    break;
                }
            }

            if (!$allColumnsExist) {
                continue;
            }

            // Check if index already exists
            if (!$this->indexExists($index['table'], $index['name'])) {
                try {
                    $columns = implode(', ', $index['columns']);
                    DB::statement("ALTER TABLE `{$index['table']}` ADD FULLTEXT INDEX `{$index['name']}` ({$columns})");
                } catch (\Exception $e) {
                    // Ignore errors
                }
            }
        }
    }

    /**
     * Check if an index exists.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            return false; // FULLTEXT doesn't exist in SQLite
        }

        $indexes = $connection->select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        $indexNames = [
            ['table' => 'clients', 'name' => 'clients_fulltext_search'],
            ['table' => 'offers', 'name' => 'offers_fulltext_search'],
            ['table' => 'contracts', 'name' => 'contracts_fulltext_search'],
            ['table' => 'access_credentials', 'name' => 'credentials_fulltext_search'],
        ];

        foreach ($indexNames as $index) {
            if (!Schema::hasTable($index['table'])) {
                continue;
            }

            try {
                DB::statement("ALTER TABLE `{$index['table']}` DROP INDEX IF EXISTS `{$index['name']}`");
            } catch (\Exception $e) {
                // Ignore
            }
        }
    }
};
