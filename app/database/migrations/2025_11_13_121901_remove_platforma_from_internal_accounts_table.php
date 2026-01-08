<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First drop the index that references this column (if it exists)
        try {
            Schema::table('internal_accounts', function (Blueprint $table) {
                $table->dropIndex('internal_accounts_platform_index');
            });
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }

        // Now drop the column
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

        Schema::table('internal_accounts', function (Blueprint $table) {
            $table->index('platforma', 'internal_accounts_platform_index');
        });
    }
};
