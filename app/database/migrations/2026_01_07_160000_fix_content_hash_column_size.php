<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix content_hash column size in contract_versions table.
 *
 * The column was defined as VARCHAR(32) for MD5 hashes,
 * but the code uses SHA256 which produces 64 character hashes.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contract_versions', function (Blueprint $table) {
            $table->string('content_hash', 64)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_versions', function (Blueprint $table) {
            $table->string('content_hash', 32)->change();
        });
    }
};
