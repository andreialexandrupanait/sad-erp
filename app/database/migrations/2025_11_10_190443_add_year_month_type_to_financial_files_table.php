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
        Schema::table('financial_files', function (Blueprint $table) {
            $table->integer('an')->nullable()->after('entity_id'); // Year
            $table->integer('luna')->nullable()->after('an'); // Month
            $table->string('tip', 50)->nullable()->after('luna'); // Type: incasare, plata, extrase, general
            $table->string('file_url')->nullable()->after('file_path'); // Public/signed URL
            $table->string('mime_type', 100)->nullable()->after('file_type'); // More specific MIME type

            // Add composite index for year/month queries
            $table->index(['an', 'luna']);
            // Add index for type filtering
            $table->index('tip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_files', function (Blueprint $table) {
            $table->dropIndex(['an', 'luna']);
            $table->dropIndex(['tip']);
            $table->dropColumn(['an', 'luna', 'tip', 'file_url', 'mime_type']);
        });
    }
};
