<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add lock_version column for optimistic locking.
 *
 * This prevents race conditions when multiple users try to edit
 * the same contract simultaneously. Each save increments the version,
 * and concurrent edits are detected by version mismatch.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('lock_version')->default(1)->after('locked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('lock_version');
        });
    }
};
