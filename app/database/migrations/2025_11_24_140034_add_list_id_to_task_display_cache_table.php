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
        Schema::table('task_display_cache', function (Blueprint $table) {
            $table->foreignId('list_id')->nullable()->after('status_id')->constrained('task_lists')->cascadeOnDelete();
            $table->index(['organization_id', 'list_id', 'status_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_display_cache', function (Blueprint $table) {
            $table->dropForeign(['list_id']);
            $table->dropIndex(['organization_id', 'list_id', 'status_id']);
            $table->dropColumn('list_id');
        });
    }
};
