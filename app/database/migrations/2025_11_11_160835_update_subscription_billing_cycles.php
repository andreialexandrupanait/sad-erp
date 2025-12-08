<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Update billing_cycle enum to add 'saptamanal' (weekly) option
     * New options: saptamanal, lunar (monthly), anual, custom
     */
    public function up(): void
    {
        // Skip for SQLite (testing environment)
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE subscriptions MODIFY COLUMN billing_cycle ENUM('saptamanal', 'lunar', 'anual', 'custom') NOT NULL DEFAULT 'lunar'");
        }

        // Update existing data to match new enum values
        DB::table('subscriptions')->where('billing_cycle', 'monthly')->update(['billing_cycle' => 'lunar']);
        DB::table('subscriptions')->where('billing_cycle', 'annual')->update(['billing_cycle' => 'anual']);
    }

    /**
     * Reverse the migrations.
     *
     * Revert back to original enum values
     */
    public function down(): void
    {
        // Update data back to old values
        DB::table('subscriptions')->where('billing_cycle', 'lunar')->update(['billing_cycle' => 'monthly']);
        DB::table('subscriptions')->where('billing_cycle', 'anual')->update(['billing_cycle' => 'annual']);
        DB::table('subscriptions')->where('billing_cycle', 'saptamanal')->update(['billing_cycle' => 'custom']);

        // Skip for SQLite (testing environment)
        if (DB::connection()->getDriverName() !== 'sqlite') {
            // Revert enum
            DB::statement("ALTER TABLE subscriptions MODIFY COLUMN billing_cycle ENUM('monthly', 'annual', 'custom') NOT NULL DEFAULT 'monthly'");
        }
    }
};
