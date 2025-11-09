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
        Schema::create('subscription_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id'); // No foreign key constraint - logs persist even if subscription deleted
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');

            $table->date('old_renewal_date'); // Data veche de renewal
            $table->date('new_renewal_date'); // Data noua de renewal
            $table->string('change_reason'); // Motivul schimbarii (ex: "Auto-updated by system", "Manual update")
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->onDelete('set null'); // Who made the change

            $table->timestamp('changed_at'); // When the change happened

            // Indexes for performance
            $table->index('subscription_id');
            $table->index(['organization_id', 'subscription_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_logs');
    }
};
