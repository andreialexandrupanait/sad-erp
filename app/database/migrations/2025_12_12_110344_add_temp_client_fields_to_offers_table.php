<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, drop the foreign key constraint
        Schema::table('offers', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        // Make client_id nullable and add temp client fields
        Schema::table('offers', function (Blueprint $table) {
            // Modify client_id to be nullable
            $table->unsignedBigInteger('client_id')->nullable()->change();

            // Add temporary client fields (used when client_id is null)
            $table->string('temp_client_name')->nullable()->after('client_id');
            $table->string('temp_client_email')->nullable()->after('temp_client_name');
            $table->string('temp_client_phone')->nullable()->after('temp_client_email');
            $table->string('temp_client_company')->nullable()->after('temp_client_phone');
        });

        // Re-add foreign key without cascade delete (to allow null)
        Schema::table('offers', function (Blueprint $table) {
            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn([
                'temp_client_name',
                'temp_client_email',
                'temp_client_phone',
                'temp_client_company',
            ]);
        });

        // Note: Cannot restore NOT NULL constraint if null values exist
        Schema::table('offers', function (Blueprint $table) {
            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->cascadeOnDelete();
        });
    }
};
