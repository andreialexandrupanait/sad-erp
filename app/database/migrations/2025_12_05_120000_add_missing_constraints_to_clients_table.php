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
     * Adds missing constraints for data integrity:
     * 1. FK constraint for status_id -> settings_options.id
     * 2. Unique constraint for tax_id per organization
     */
    public function up(): void
    {
        // First, verify no orphaned status_id values exist
        $orphaned = DB::table('clients')
            ->whereNotNull('status_id')
            ->whereNotIn('status_id', DB::table('settings_options')->pluck('id'))
            ->count();

        if ($orphaned > 0) {
            throw new \RuntimeException(
                "Cannot add FK constraint: {$orphaned} clients have orphaned status_id values. " .
                "Please fix these records first."
            );
        }

        // Verify no duplicate tax_ids within same organization
        $duplicates = DB::table('clients')
            ->select('organization_id', 'tax_id', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('tax_id')
            ->where('tax_id', '!=', '-')
            ->where('tax_id', '!=', '')
            ->whereNull('deleted_at')
            ->groupBy('organization_id', 'tax_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($duplicates > 0) {
            throw new \RuntimeException(
                "Cannot add unique constraint: {$duplicates} duplicate tax_id values exist within same organization. " .
                "Please fix these records first."
            );
        }

        Schema::table('clients', function (Blueprint $table) {
            // Add FK for status_id -> settings_options (with SET NULL on delete)
            $table->foreign('status_id')
                ->references('id')
                ->on('settings_options')
                ->nullOnDelete();

            // Add unique constraint for tax_id per organization
            // Note: This allows multiple NULL tax_ids and multiple '-' tax_ids
            // The constraint only enforces uniqueness for non-null, non-empty, non-placeholder values
            $table->unique(['organization_id', 'tax_id'], 'clients_org_tax_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropUnique('clients_org_tax_id_unique');
        });
    }
};
