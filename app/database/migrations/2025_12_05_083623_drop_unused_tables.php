<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drop unused/orphaned tables identified in database audit.
     */
    public function up(): void
    {
        // First, drop foreign key constraints that reference tables we're dropping
        Schema::table('financial_expenses', function (Blueprint $table) {
            $table->dropForeign(['recurring_expense_id']);
            $table->dropColumn('recurring_expense_id');
        });

        // ClickUp integration tables (integration removed)
        Schema::dropIfExists('clickup_mappings');
        Schema::dropIfExists('clickup_syncs');

        // Banking tables - KEPT: Feature is being developed
        // Schema::dropIfExists('bank_sync_logs');
        // Schema::dropIfExists('bank_statements');
        // Schema::dropIfExists('bank_transactions');
        // Schema::dropIfExists('banking_credentials');

        // Orphaned feature tables (never implemented)
        Schema::dropIfExists('client_service_rates');
        Schema::dropIfExists('financial_alerts');
        Schema::dropIfExists('recurring_expenses');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('files');

        // Unused Laravel tables
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        // password_reset_tokens - KEPT: Required for Laravel password reset functionality
        // Schema::dropIfExists('password_reset_tokens');
    }

    /**
     * Reverse the migrations.
     * Note: Tables are intentionally not recreated as they were unused.
     */
    public function down(): void
    {
        // Tables were empty and unused - no rollback needed
    }
};
