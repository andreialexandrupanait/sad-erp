<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Normalize Romanian column names to English for consistency.
     * - nume_cont_aplicatie → account_name
     * - accesibil_echipei → team_accessible
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite doesn't support CHANGE COLUMN - use Laravel's rename
            Schema::table('internal_accounts', function (Blueprint $table) {
                $table->renameColumn('nume_cont_aplicatie', 'account_name');
                $table->renameColumn('accesibil_echipei', 'team_accessible');
            });
            return;
        }

        DB::statement('ALTER TABLE internal_accounts CHANGE nume_cont_aplicatie account_name VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE internal_accounts CHANGE accesibil_echipei team_accessible TINYINT(1) NOT NULL DEFAULT 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table('internal_accounts', function (Blueprint $table) {
                $table->renameColumn('account_name', 'nume_cont_aplicatie');
                $table->renameColumn('team_accessible', 'accesibil_echipei');
            });
            return;
        }

        DB::statement('ALTER TABLE internal_accounts CHANGE account_name nume_cont_aplicatie VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE internal_accounts CHANGE team_accessible accesibil_echipei TINYINT(1) NOT NULL DEFAULT 0');
    }
};
