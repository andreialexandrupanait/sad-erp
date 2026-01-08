<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Make client_id nullable to support contracts from offers with temporary clients.
     */
    public function up(): void
    {
        // Drop the foreign key first
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        // Make client_id nullable
        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->change();
        });

        // Re-add foreign key with nullOnDelete
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->nullOnDelete();
        });

        // Add temp client fields for contracts without a linked client
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('temp_client_name')->nullable()->after('client_id');
            $table->string('temp_client_email')->nullable()->after('temp_client_name');
            $table->string('temp_client_company')->nullable()->after('temp_client_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['temp_client_name', 'temp_client_email', 'temp_client_company']);
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->cascadeOnDelete();
        });
    }
};
