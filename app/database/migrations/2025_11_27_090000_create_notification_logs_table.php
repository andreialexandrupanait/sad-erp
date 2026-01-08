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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Notification identification
            $table->string('notification_type', 100); // e.g., 'subscription_renewal_30d', 'domain_expiring_7d'
            $table->string('channel', 50); // e.g., 'slack', 'whatsapp'

            // Entity reference (what triggered this notification)
            $table->string('entity_type', 100)->nullable(); // e.g., 'Domain', 'Subscription', 'FinancialRevenue'
            $table->unsignedBigInteger('entity_id')->nullable();

            // Notification data
            $table->json('payload')->nullable(); // The message that was sent

            // Status tracking
            $table->string('status', 20); // 'sent', 'failed', 'skipped'
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['organization_id', 'notification_type']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('sent_at');
            $table->index(['entity_type', 'entity_id', 'notification_type', 'channel', 'status'], 'notif_logs_dedup_idx'); // For duplicate detection
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
