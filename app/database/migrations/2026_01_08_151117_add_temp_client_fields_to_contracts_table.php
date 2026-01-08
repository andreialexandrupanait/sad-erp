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
            $table->text('temp_client_address')->nullable()->after('temp_client_company');
            $table->string('temp_client_tax_id')->nullable()->after('temp_client_address');
            $table->string('temp_client_registration_number')->nullable()->after('temp_client_tax_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['temp_client_address', 'temp_client_tax_id', 'temp_client_registration_number']);
        });
    }
};
