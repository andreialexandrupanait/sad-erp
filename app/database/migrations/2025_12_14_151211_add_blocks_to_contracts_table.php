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
        Schema::table('contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('contracts', 'blocks')) {
                $table->json('blocks')->nullable()->after('content');
            }
            if (!Schema::hasColumn('contracts', 'editor_settings')) {
                $table->json('editor_settings')->nullable()->after('blocks');
            }
            if (!Schema::hasColumn('contracts', 'contract_template_id')) {
                $table->foreignId('contract_template_id')->nullable()->after('template_id')->constrained('contract_templates')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['contract_template_id']);
            $table->dropColumn(['blocks', 'editor_settings', 'contract_template_id']);
        });
    }
};
