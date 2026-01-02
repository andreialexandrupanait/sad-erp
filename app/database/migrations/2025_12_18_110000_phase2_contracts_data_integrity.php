<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Phase 2: Data Integrity fixes for Contracts module.
 *
 * Changes:
 * 1. Add parent_contract_id for renewal tracking
 * 2. Add missing performance indexes
 * 3. Add soft deletes to contract_items
 * 4. Add unique constraint for contract_number within organization (race condition fix)
 */
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

        // MySQL/MariaDB
        $indexes = $connection->select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Safely add an index (ignores if already exists).
     */
    private function safeAddIndex(string $table, string|array $columns, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            try {
                Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                    $t->index($columns, $indexName);
                });
            } catch (\Exception $e) {
                // Index might already exist, ignore
            }
        }
    }

    /**
     * Safely drop an index (ignores if doesn't exist).
     */
    private function safeDropIndex(string $table, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            try {
                Schema::table($table, function (Blueprint $t) use ($indexName) {
                    $t->dropIndex($indexName);
                });
            } catch (\Exception $e) {
                // Index might not exist, ignore
            }
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add parent_contract_id for renewal tracking
        Schema::table('contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('contracts', 'parent_contract_id')) {
                $table->foreignId('parent_contract_id')
                    ->nullable()
                    ->after('organization_id')
                    ->constrained('contracts')
                    ->nullOnDelete();
            }
        });

        // 2. Add missing performance indexes to contracts
        $this->safeAddIndex('contracts', 'contract_number', 'contracts_contract_number_index');
        $this->safeAddIndex('contracts', 'end_date', 'contracts_end_date_index');
        $this->safeAddIndex('contracts', 'offer_id', 'contracts_offer_id_index');

        // 3. Add soft deletes to contract_items
        if (Schema::hasTable('contract_items')) {
            Schema::table('contract_items', function (Blueprint $table) {
                if (!Schema::hasColumn('contract_items', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // 4. Drop old unique constraint on contract_number and add composite unique
        // This allows same contract numbers in different organizations
        $this->safeDropIndex('contracts', 'contracts_contract_number_unique');

        // Add composite unique constraint (organization_id + contract_number)
        if (!$this->indexExists('contracts', 'contracts_org_number_unique')) {
            try {
                Schema::table('contracts', function (Blueprint $table) {
                    $table->unique(['organization_id', 'contract_number'], 'contracts_org_number_unique');
                });
            } catch (\Exception $e) {
                // Unique constraint might already exist, ignore
            }
        }

        // 5. Add index for parent_contract_id
        if (Schema::hasColumn('contracts', 'parent_contract_id')) {
            $this->safeAddIndex('contracts', 'parent_contract_id', 'contracts_parent_contract_id_index');
        }

        // 6. Add index for contract_annexes.offer_id
        if (Schema::hasTable('contract_annexes') && Schema::hasColumn('contract_annexes', 'offer_id')) {
            $this->safeAddIndex('contract_annexes', 'offer_id', 'contract_annexes_offer_id_index');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove contract_annexes index
        if (Schema::hasTable('contract_annexes')) {
            $this->safeDropIndex('contract_annexes', 'contract_annexes_offer_id_index');
        }

        // Remove composite unique and restore simple unique
        $this->safeDropIndex('contracts', 'contracts_org_number_unique');

        // Restore simple unique constraint
        if (!$this->indexExists('contracts', 'contracts_contract_number_unique')) {
            try {
                Schema::table('contracts', function (Blueprint $table) {
                    $table->unique('contract_number', 'contracts_contract_number_unique');
                });
            } catch (\Exception $e) {
                // Ignore
            }
        }

        // Remove soft deletes from contract_items
        if (Schema::hasTable('contract_items')) {
            Schema::table('contract_items', function (Blueprint $table) {
                if (Schema::hasColumn('contract_items', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }

        // Remove indexes
        $this->safeDropIndex('contracts', 'contracts_contract_number_index');
        $this->safeDropIndex('contracts', 'contracts_end_date_index');
        $this->safeDropIndex('contracts', 'contracts_offer_id_index');
        $this->safeDropIndex('contracts', 'contracts_parent_contract_id_index');

        // Remove parent_contract_id
        Schema::table('contracts', function (Blueprint $table) {
            if (Schema::hasColumn('contracts', 'parent_contract_id')) {
                $table->dropForeign(['parent_contract_id']);
                $table->dropColumn('parent_contract_id');
            }
        });
    }
};
