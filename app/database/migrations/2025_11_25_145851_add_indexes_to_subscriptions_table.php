<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds missing indexes to improve query performance
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Index for user-based global scope filtering
            if (!$this->hasIndex('subscriptions', 'subscriptions_user_id_index')) {
                $table->index('user_id', 'subscriptions_user_id_index');
            }

            // Index for status filtering (frequently used in queries)
            if (!$this->hasIndex('subscriptions', 'subscriptions_status_index')) {
                $table->index('status', 'subscriptions_status_index');
            }

            // Index for soft deletes
            if (!$this->hasIndex('subscriptions', 'subscriptions_deleted_at_index')) {
                $table->index('deleted_at', 'subscriptions_deleted_at_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('subscriptions_user_id_index');
            $table->dropIndex('subscriptions_status_index');
            $table->dropIndex('subscriptions_deleted_at_index');
        });
    }

    /**
     * Check if an index exists on the table
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if ($index['name'] === $indexName) {
                return true;
            }
        }
        return false;
    }
};
