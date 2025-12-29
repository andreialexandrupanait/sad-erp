<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add FULLTEXT indexes for search optimization.
     *
     * FULLTEXT indexes provide 10-100x performance improvement over LIKE queries
     * for text search operations. They enable natural language search and
     * boolean mode searching.
     *
     * Benefits:
     * - Much faster than LIKE '%search%' queries
     * - Support for relevance ranking
     * - Support for boolean operators (+word -word "exact phrase")
     * - Better user experience with instant search results
     */
    public function up(): void
    {
        // Check MySQL version - FULLTEXT requires MySQL 5.6+ with InnoDB
        $version = DB::select("SELECT VERSION() as version")[0]->version;

        if (version_compare($version, '5.6', '<')) {
            echo "Skipping FULLTEXT indexes - requires MySQL 5.6 or higher\n";
            return;
        }

        $fulltextIndexes = [
            [
                'table' => 'clients',
                'columns' => ['name', 'company_name', 'email'],
                'name' => 'clients_fulltext_search',
            ],
            [
                'table' => 'offers',
                'columns' => ['title', 'description'],
                'name' => 'offers_fulltext_search',
            ],
            [
                'table' => 'contracts',
                'columns' => ['title', 'description'],
                'name' => 'contracts_fulltext_search',
            ],
            [
                'table' => 'access_credentials',
                'columns' => ['site_name', 'platform', 'username', 'url'],
                'name' => 'credentials_fulltext_search',
            ],
        ];

        foreach ($fulltextIndexes as $index) {
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

            // Check if all columns exist
            $allColumnsExist = true;
            foreach ($index['columns'] as $column) {
                $columnExists = DB::select("
                    SELECT COUNT(*) as count
                    FROM information_schema.columns
                    WHERE table_schema = DATABASE()
                    AND table_name = ?
                    AND column_name = ?
                ", [$index['table'], $column]);

                if ($columnExists[0]->count == 0) {
                    $allColumnsExist = false;
                    echo "Skipping FULLTEXT index {$index['name']} - column {$column} doesn't exist\n";
                    break;
                }
            }

            if (!$allColumnsExist) {
                continue;
            }

            // Check if FULLTEXT index already exists
            $indexExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = ?
                AND index_name = ?
            ", [$index['table'], $index['name']]);

            if ($indexExists[0]->count == 0) {
                try {
                    $columns = implode(', ', $index['columns']);
                    DB::statement("ALTER TABLE `{$index['table']}` ADD FULLTEXT INDEX `{$index['name']}` ({$columns})");
                    echo "Created FULLTEXT index: {$index['name']}\n";
                } catch (\Exception $e) {
                    echo "Failed to create FULLTEXT index {$index['name']}: " . $e->getMessage() . "\n";
                }
            } else {
                echo "FULLTEXT index {$index['name']} already exists\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexNames = [
            ['table' => 'clients', 'name' => 'clients_fulltext_search'],
            ['table' => 'offers', 'name' => 'offers_fulltext_search'],
            ['table' => 'contracts', 'name' => 'contracts_fulltext_search'],
            ['table' => 'access_credentials', 'name' => 'credentials_fulltext_search'],
        ];

        foreach ($indexNames as $index) {
            try {
                DB::statement("ALTER TABLE `{$index['table']}` DROP INDEX IF EXISTS `{$index['name']}`");
            } catch (\Exception $e) {
                // Ignore errors on drop
            }
        }
    }
};
