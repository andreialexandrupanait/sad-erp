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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Drop foreign key and indexes first
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id', 'user_id']);
            $table->dropIndex(['organization_id', 'status']);

            // Then drop the column
            $table->dropColumn('organization_id');

            // Add new index for user_id only
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Re-add organization_id column
            $table->unsignedBigInteger('organization_id')->after('id');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');

            // Drop user_id index
            $table->dropIndex(['user_id']);

            // Re-add composite indexes
            $table->index(['organization_id', 'user_id']);
            $table->index(['organization_id', 'status']);
        });
    }
};
