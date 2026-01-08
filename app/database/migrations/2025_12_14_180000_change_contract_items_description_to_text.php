<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change description column from varchar(255) to text to support longer descriptions.
     */
    public function up(): void
    {
        Schema::table('contract_items', function (Blueprint $table) {
            $table->text('description')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_items', function (Blueprint $table) {
            $table->string('description')->change();
        });
    }
};
