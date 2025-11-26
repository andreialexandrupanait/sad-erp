<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Convert ENUM columns to VARCHAR for better flexibility.
     * This allows status/type values to be managed through SettingOption
     * without needing database migrations for each new option.
     */
    public function up(): void
    {
        // Domains: Convert status ENUM to VARCHAR
        // Current ENUM: 'Active', 'Expiring', 'Expired', 'Suspended'
        DB::statement("ALTER TABLE domains MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Active'");

        // Subscriptions: Convert status ENUM to VARCHAR
        // Current ENUM: 'active', 'paused', 'cancelled'
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'active'");

        // Subscriptions: Convert billing_cycle ENUM to VARCHAR
        // Current ENUM: 'monthly', 'annual', 'custom', possibly 'weekly'
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN billing_cycle VARCHAR(50) NOT NULL DEFAULT 'monthly'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to ENUMs (note: this may fail if data contains values outside the ENUM)
        DB::statement("ALTER TABLE domains MODIFY COLUMN status ENUM('Active', 'Expiring', 'Expired', 'Suspended') NOT NULL DEFAULT 'Active'");
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status ENUM('active', 'paused', 'cancelled') NOT NULL DEFAULT 'active'");
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN billing_cycle ENUM('monthly', 'annual', 'custom', 'weekly') NOT NULL DEFAULT 'monthly'");
    }
};
