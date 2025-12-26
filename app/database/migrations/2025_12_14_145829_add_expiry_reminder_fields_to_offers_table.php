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
            $table->timestamp('expiry_reminder_sent_at')->nullable()->after('current_version');
            $table->boolean('expiry_reminder_enabled')->default(true)->after('expiry_reminder_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['expiry_reminder_sent_at', 'expiry_reminder_enabled']);
        });
    }
};
