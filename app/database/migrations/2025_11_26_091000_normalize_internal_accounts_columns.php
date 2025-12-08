<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
        // Skip for SQLite - columns already use English names in test environment
        if (DB::connection()->getDriverName() === 'sqlite') {
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
        // Skip for SQLite - columns use English names in test environment
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE internal_accounts CHANGE account_name nume_cont_aplicatie VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE internal_accounts CHANGE team_accessible accesibil_echipei TINYINT(1) NOT NULL DEFAULT 0');
    }
};
