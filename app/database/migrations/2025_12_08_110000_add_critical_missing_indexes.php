<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check and add index on clients.status_id
        if (!$this->indexExists('clients', 'clients_status_id_index')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->index('status_id', 'clients_status_id_index');
            });
        }

        // Check and add index on subscriptions.status
        if (!$this->indexExists('subscriptions', 'subscriptions_status_index')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->index('status', 'subscriptions_status_index');
            });
        }

        // Check and add index on domains.client_id
        if (!$this->indexExists('domains', 'domains_client_id_index')) {
            Schema::table('domains', function (Blueprint $table) {
                $table->index('client_id', 'domains_client_id_index');
            });
        }

        // Check and add index on access_credentials.client_id
        if (!$this->indexExists('access_credentials', 'access_credentials_client_id_index')) {
            Schema::table('access_credentials', function (Blueprint $table) {
                $table->index('client_id', 'access_credentials_client_id_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('clients_status_id_index');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('subscriptions_status_index');
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->dropIndex('domains_client_id_index');
        });

        Schema::table('access_credentials', function (Blueprint $table) {
            $table->dropIndex('access_credentials_client_id_index');
        });
    }

    /**
     * Check if an index exists on a table (Laravel 12 compatible)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Query sqlite_master table
            $indexes = DB::select(
                "SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = ? AND name = ?",
                [$table, $indexName]
            );
            return count($indexes) > 0;
        }

        // MySQL
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
