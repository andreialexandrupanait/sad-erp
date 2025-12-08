<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip for SQLite - column was never added in SQLite migrations
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('internal_accounts', function (Blueprint $table) {
            $table->dropColumn('platforma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internal_accounts', function (Blueprint $table) {
            $table->string('platforma')->nullable();
        });
    }
};
