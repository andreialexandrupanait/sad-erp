<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Add missing performance indexes identified in production review.
 *
 * Issues addressed:
 * - client_notes: Missing indexes on client_id, user_id
 * - offer_items: Missing index on service_id
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // Skip for SQLite as it handles indexes differently during tests
        if ($driver === 'sqlite') {
            return;
        }

        // client_notes table indexes
        $this->addIndexSafely('client_notes', 'client_id', 'client_notes_client_id_index');
        $this->addIndexSafely('client_notes', 'user_id', 'client_notes_user_id_index');

        // offer_items table indexes
        $this->addIndexSafely('offer_items', 'service_id', 'offer_items_service_id_index');
    }

    /**
     * Add an index safely, ignoring if it already exists.
     */
    private function addIndexSafely(string $table, string|array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        } catch (\Exception $e) {
            // Index already exists or other error, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        $this->dropIndexSafely('client_notes', 'client_notes_client_id_index');
        $this->dropIndexSafely('client_notes', 'client_notes_user_id_index');
        $this->dropIndexSafely('offer_items', 'offer_items_service_id_index');
    }

    /**
     * Drop an index safely, ignoring if it doesn't exist.
     */
    private function dropIndexSafely(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropIndex($indexName);
            });
        } catch (\Exception $e) {
            // Index doesn't exist or other error, ignore
        }
    }
};
