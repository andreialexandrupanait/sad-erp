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
            $table->foreignId('recurring_expense_id')->nullable()->after('category_option_id')
                ->constrained('recurring_expenses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_expenses', function (Blueprint $table) {
            $table->dropForeign(['recurring_expense_id']);
            $table->dropColumn('recurring_expense_id');
        });
    }
};
