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
            $table->enum('language', ['ro', 'en'])->default('ro')->after('currency');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->enum('language', ['ro', 'en'])->default('ro')->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('language');
        });
    }
};
