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
        Schema::table('offer_items', function (Blueprint $table) {
            if (!Schema::hasColumn('offer_items', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('contract_items', function (Blueprint $table) {
            if (!Schema::hasColumn('contract_items', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('contract_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
