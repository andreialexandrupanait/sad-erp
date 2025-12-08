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
        Schema::table('financial_expenses', function (Blueprint $table) {
            $table->foreignId('source_file_id')->nullable()->after('note')
                ->constrained('financial_files')->nullOnDelete();
            $table->index('source_file_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_expenses', function (Blueprint $table) {
            $table->dropForeign(['source_file_id']);
            $table->dropColumn('source_file_id');
        });
    }
};
