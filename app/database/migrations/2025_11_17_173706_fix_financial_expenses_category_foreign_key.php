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
            // Add foreign key constraint pointing to settings_options
            // (The old constraint to financial_settings was already dropped when that table was deleted)
            $table->foreign('category_option_id')
                  ->references('id')
                  ->on('settings_options')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_expenses', function (Blueprint $table) {
            // Drop the foreign key constraint to settings_options
            $table->dropForeign(['category_option_id']);

            // Note: We cannot restore the old constraint to financial_settings
            // as that table no longer exists after consolidation
        });
    }
};
