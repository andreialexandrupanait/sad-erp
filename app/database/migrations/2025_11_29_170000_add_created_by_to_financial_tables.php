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
        // Add created_by to financial_revenues
        if (!Schema::hasColumn('financial_revenues', 'created_by')) {
            Schema::table('financial_revenues', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            });

            // Copy user_id to created_by for existing records
            DB::statement('UPDATE financial_revenues SET created_by = user_id WHERE created_by IS NULL');
        }

        // Add created_by to financial_expenses
        if (!Schema::hasColumn('financial_expenses', 'created_by')) {
            Schema::table('financial_expenses', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            });

            // Copy user_id to created_by for existing records
            DB::statement('UPDATE financial_expenses SET created_by = user_id WHERE created_by IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_revenues', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });

        Schema::table('financial_expenses', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
