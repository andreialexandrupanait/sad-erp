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
        Schema::table('financial_revenues', function (Blueprint $table) {
            $table->string('smartbill_invoice_number')->nullable()->after('document_name');
            $table->string('smartbill_series')->nullable()->after('smartbill_invoice_number');
            $table->string('smartbill_client_cif')->nullable()->after('smartbill_series');
            $table->timestamp('smartbill_imported_at')->nullable()->after('smartbill_client_cif');
            $table->json('smartbill_raw_data')->nullable()->after('smartbill_imported_at');

            // Create index for faster lookups
            $table->index(['organization_id', 'smartbill_invoice_number'], 'idx_org_smartbill_invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_revenues', function (Blueprint $table) {
            $table->dropIndex('idx_org_smartbill_invoice');
            $table->dropColumn([
                'smartbill_invoice_number',
                'smartbill_series',
                'smartbill_client_cif',
                'smartbill_imported_at',
                'smartbill_raw_data',
            ]);
        });
    }
};
