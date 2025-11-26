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
        Schema::table('tasks', function (Blueprint $table) {
            // Add start_date after due_date
            $table->date('start_date')->nullable()->after('due_date');

            // Add time_estimate after time_tracked
            $table->integer('time_estimate')->nullable()->comment('Estimated time in minutes')->after('time_tracked');

            // Add date_closed after updated_at
            $table->timestamp('date_closed')->nullable()->after('updated_at');

            // Add index on start_date for performance
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['start_date']);
            $table->dropColumn(['start_date', 'time_estimate', 'date_closed']);
        });
    }
};
