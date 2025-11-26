<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix dangerous cascade delete foreign keys on financial tables.
 *
 * ISSUE: Current FK constraints use cascadeOnDelete() which means:
 * - Deleting a user permanently deletes ALL their financial records (bypassing SoftDeletes)
 * - Deleting an organization permanently deletes ALL financial data
 *
 * FIX: Change to nullOnDelete() so deleting a user/org sets the FK to NULL
 * instead of permanently deleting financial records.
 *
 * SAFE: This migration modifies FK behavior only, no data is modified or deleted.
 * Records remain intact, only the deletion behavior changes.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix financial_revenues foreign keys
        Schema::table('financial_revenues', function (Blueprint $table) {
            // First, make user_id nullable if it isn't already
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Drop existing FK and recreate with nullOnDelete
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // Fix financial_expenses foreign keys
        Schema::table('financial_expenses', function (Blueprint $table) {
            // First, make user_id nullable if it isn't already
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Drop existing FK and recreate with nullOnDelete
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // Fix financial_files foreign keys
        Schema::table('financial_files', function (Blueprint $table) {
            // First, make user_id nullable if it isn't already
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Drop existing FK and recreate with nullOnDelete
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // Note: organization_id FK is kept as CASCADE because:
        // 1. Deleting an entire organization is a rare, intentional operation
        // 2. Financial data without an organization has no context
        // 3. The global scope wouldn't work without org_id anyway
        // If you want to change org FK too, uncomment below:
        //
        // Schema::table('financial_revenues', function (Blueprint $table) {
        //     $table->unsignedBigInteger('organization_id')->nullable()->change();
        //     $table->dropForeign(['organization_id']);
        //     $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore cascade delete on user_id for financial_revenues
        Schema::table('financial_revenues', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        // Restore cascade delete on user_id for financial_expenses
        Schema::table('financial_expenses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        // Restore cascade delete on user_id for financial_files
        Schema::table('financial_files', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
