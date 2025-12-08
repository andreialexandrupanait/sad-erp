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
            // Add composite index for fast duplicate detection during Smartbill imports
            // This index covers: organization + series + invoice number
            $table->index(
                ['organization_id', 'smartbill_series', 'smartbill_invoice_number'],
                'idx_org_series_invoice'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_revenues', function (Blueprint $table) {
            $table->dropIndex('idx_org_series_invoice');
        });
    }
};
