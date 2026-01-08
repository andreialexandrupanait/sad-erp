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
        Schema::table('offers', function (Blueprint $table) {
            $table->string('temp_client_bank_account')->nullable()->after('temp_client_registration_number');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->string('temp_client_bank_account')->nullable()->after('temp_client_registration_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('temp_client_bank_account');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('temp_client_bank_account');
        });
    }
};
