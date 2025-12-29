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
     * Fix cascade delete conflicts where both parent and child use SoftDeletes.
     *
     * Problem: When a model uses SoftDeletes, force deleting it will cascade delete
     * related records, even if those records also use SoftDeletes. This bypasses
     * the soft delete mechanism and causes permanent data loss.
     *
     * Solution: Change CASCADE to SET NULL for relationships where:
     * 1. Both parent and child use SoftDeletes
     * 2. The child can logically exist without the parent
     */
    public function up(): void
    {
        // Fix: access_credentials.client_id
        // Credentials should survive when a client is deleted
        if (Schema::hasTable('access_credentials')) {
            Schema::table('access_credentials', function (Blueprint $table) {
                // Drop existing foreign key
                try {
                    $table->dropForeign(['client_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }

                // Make client_id nullable if it isn't already
                DB::statement('ALTER TABLE access_credentials MODIFY client_id BIGINT UNSIGNED NULL');

                // Re-add foreign key with SET NULL
                $table->foreign('client_id')
                    ->references('id')
                    ->on('clients')
                    ->onDelete('SET NULL')
                    ->onUpdate('NO ACTION');
            });
        }

        // Note: The following relationships use CASCADE which is ACCEPTABLE:
        //
        // Offers and related tables:
        // - offer_items.offer_id CASCADE (correct - items belong to offer)
        // - offer_activities.offer_id CASCADE (correct - activity log belongs to offer)
        // - offer_versions.offer_id CASCADE (correct - versions belong to offer)
        //
        // Contracts and related tables:
        // - contract_items.contract_id CASCADE (correct - items belong to contract)
        // - contract_activities.contract_id CASCADE (correct - activity log belongs to contract)
        // - contract_versions.contract_id CASCADE (correct - versions belong to contract)
        // - contract_annexes.contract_id CASCADE (correct - annexes belong to contract)
        //
        // These are acceptable because:
        // 1. Child records have no meaning without the parent
        // 2. They should be deleted when parent is force-deleted
        // 3. Soft-deleting the parent doesn't trigger cascade (only force delete does)
        //
        // Organization cascades are also acceptable - when an organization is deleted,
        // all its data should be deleted (this is a rare administrative action).
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('access_credentials')) {
            Schema::table('access_credentials', function (Blueprint $table) {
                // Drop SET NULL foreign key
                try {
                    $table->dropForeign(['client_id']);
                } catch (\Exception $e) {
                    // Ignore if doesn't exist
                }

                // Re-add CASCADE foreign key (original behavior)
                $table->foreign('client_id')
                    ->references('id')
                    ->on('clients')
                    ->onDelete('CASCADE')
                    ->onUpdate('NO ACTION');
            });
        }
    }
};
