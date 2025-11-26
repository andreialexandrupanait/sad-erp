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
            $table->string('clickup_id')->unique()->nullable()->after('id');
            $table->string('clickup_url')->nullable()->after('clickup_id');
            $table->timestamp('clickup_imported_at')->nullable()->after('clickup_url');
            $table->json('clickup_metadata')->nullable()->after('clickup_imported_at');

            $table->index('clickup_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['clickup_id']);
            $table->dropColumn(['clickup_id', 'clickup_url', 'clickup_imported_at', 'clickup_metadata']);
        });
    }
};
