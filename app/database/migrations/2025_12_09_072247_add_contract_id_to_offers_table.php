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
        Schema::table('offers', function (Blueprint $table) {
            $table->foreignId('contract_id')
                ->nullable()
                ->after('template_id')
                ->constrained()
                ->nullOnDelete();

            $table->index('contract_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropForeign(['contract_id']);
            $table->dropIndex(['contract_id']);
            $table->dropColumn('contract_id');
        });
    }
};
