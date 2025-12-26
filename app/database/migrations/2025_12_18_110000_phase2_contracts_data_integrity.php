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
        Schema::table('contracts', function (Blueprint $table) {
            // Index for contract_number searches (already unique but adding for search performance)
            // Check if index exists before adding
            $indexes = collect(DB::select("SHOW INDEX FROM contracts WHERE Key_name = 'contracts_contract_number_index'"));
            if ($indexes->isEmpty()) {
                $table->index('contract_number', 'contracts_contract_number_index');
            }

            // Index for end_date queries (expiry checks)
            $endDateIndexes = collect(DB::select("SHOW INDEX FROM contracts WHERE Key_name = 'contracts_end_date_index'"));
            if ($endDateIndexes->isEmpty()) {
                $table->index('end_date', 'contracts_end_date_index');
            }

            // Index for offer_id lookups
            $offerIndexes = collect(DB::select("SHOW INDEX FROM contracts WHERE Key_name = 'contracts_offer_id_index'"));
            if ($offerIndexes->isEmpty()) {
                $table->index('offer_id', 'contracts_offer_id_index');
            }
        });

        // 3. Add soft deletes to contract_items
        Schema::table('contract_items', function (Blueprint $table) {
            if (!Schema::hasColumn('contract_items', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // 4. Drop old unique constraint on contract_number and add composite unique
        // This allows same contract numbers in different organizations
        // First check if the simple unique exists
        $uniqueExists = collect(DB::select("SHOW INDEX FROM contracts WHERE Key_name = 'contracts_contract_number_unique'"))->isNotEmpty();

        if ($uniqueExists) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->dropUnique('contracts_contract_number_unique');
            });
        }

        // Add composite unique constraint (organization_id + contract_number)
        $compositeExists = collect(DB::select("SHOW INDEX FROM contracts WHERE Key_name = 'contracts_org_number_unique'"))->isNotEmpty();

        if (!$compositeExists) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->unique(['organization_id', 'contract_number'], 'contracts_org_number_unique');
            });
        }

        // 5. Add index for parent_contract_id
        Schema::table('contracts', function (Blueprint $table) {
            $parentIndexes = collect(DB::select("SHOW INDEX FROM contracts WHERE Key_name = 'contracts_parent_contract_id_index'"));
            if ($parentIndexes->isEmpty() && Schema::hasColumn('contracts', 'parent_contract_id')) {
                $table->index('parent_contract_id', 'contracts_parent_contract_id_index');
            }
        });

        // 6. Add index for contract_annexes.offer_id
        if (Schema::hasTable('contract_annexes')) {
            $annexOfferIndex = collect(DB::select("SHOW INDEX FROM contract_annexes WHERE Key_name = 'contract_annexes_offer_id_index'"));
            if ($annexOfferIndex->isEmpty() && Schema::hasColumn('contract_annexes', 'offer_id')) {
                Schema::table('contract_annexes', function (Blueprint $table) {
                    $table->index('offer_id', 'contract_annexes_offer_id_index');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove contract_annexes index
        if (Schema::hasTable('contract_annexes')) {
            $annexOfferIndex = collect(DB::select("SHOW INDEX FROM contract_annexes WHERE Key_name = 'contract_annexes_offer_id_index'"));
            if ($annexOfferIndex->isNotEmpty()) {
                Schema::table('contract_annexes', function (Blueprint $table) {
                    $table->dropIndex('contract_annexes_offer_id_index');
                });
            }
        }

        // Remove composite unique and restore simple unique
        Schema::table('contracts', function (Blueprint $table) {
            $compositeExists = collect(DB::select("SHOW INDEX FROM contracts WHERE Key_name = 'contracts_org_number_unique'"))->isNotEmpty();
            if ($compositeExists) {
                $table->dropUnique('contracts_org_number_unique');
            }
        });

        // Restore simple unique constraint
        Schema::table('contracts', function (Blueprint $table) {
            $uniqueExists = collect(DB::select("SHOW INDEX FROM contracts WHERE Key_name = 'contracts_contract_number_unique'"))->isNotEmpty();
            if (!$uniqueExists) {
                $table->unique('contract_number', 'contracts_contract_number_unique');
            }
        });

        // Remove soft deletes from contract_items
        Schema::table('contract_items', function (Blueprint $table) {
            if (Schema::hasColumn('contract_items', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        // Remove indexes
        Schema::table('contracts', function (Blueprint $table) {
            $indexes = ['contracts_contract_number_index', 'contracts_end_date_index', 'contracts_offer_id_index', 'contracts_parent_contract_id_index'];
            foreach ($indexes as $indexName) {
                $exists = collect(DB::select("SHOW INDEX FROM contracts WHERE Key_name = ?", [$indexName]))->isNotEmpty();
                if ($exists) {
                    $table->dropIndex($indexName);
                }
            }
        });

        // Remove parent_contract_id
        Schema::table('contracts', function (Blueprint $table) {
            if (Schema::hasColumn('contracts', 'parent_contract_id')) {
                $table->dropForeign(['parent_contract_id']);
                $table->dropColumn('parent_contract_id');
            }
        });
    }
};
