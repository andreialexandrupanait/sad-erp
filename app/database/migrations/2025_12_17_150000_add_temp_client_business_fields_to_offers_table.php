<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add business-related temporary client fields to offers table.
 *
 * These fields allow storing complete client information for offers
 * where the client hasn't been saved to the database yet, enabling
 * proper contract generation with all required legal details.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            if (!Schema::hasColumn('offers', 'temp_client_address')) {
                $table->string('temp_client_address')->nullable()->after('temp_client_company');
            }
            if (!Schema::hasColumn('offers', 'temp_client_tax_id')) {
                $table->string('temp_client_tax_id')->nullable()->after('temp_client_address');
            }
            if (!Schema::hasColumn('offers', 'temp_client_registration_number')) {
                $table->string('temp_client_registration_number')->nullable()->after('temp_client_tax_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $columns = ['temp_client_address', 'temp_client_tax_id', 'temp_client_registration_number'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('offers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
