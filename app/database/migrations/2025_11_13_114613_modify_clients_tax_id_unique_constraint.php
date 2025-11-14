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
        Schema::table('clients', function (Blueprint $table) {
            // Drop the existing unique constraint
            $table->dropUnique(['tax_id', 'user_id']);

            // Note: MySQL doesn't support partial unique indexes with WHERE clause
            // We'll handle uniqueness validation in the application layer instead
            // This allows multiple clients with tax_id = NULL or '-'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Restore the original unique constraint
            $table->unique(['tax_id', 'user_id']);
        });
    }
};
