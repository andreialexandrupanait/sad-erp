<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if organization_id column already exists
        if (Schema::hasColumn('subscriptions', 'organization_id')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            // Add organization_id for organization-level scoping
            $table->foreignId('organization_id')->nullable()->after('id')->constrained()->nullOnDelete();

            // Add created_by to track who created the record (for audit, not access control)
            if (!Schema::hasColumn('subscriptions', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }

            // Add indexes for organization queries
            $table->index('organization_id');
        });

        // Migrate existing data: set organization_id based on user's organization
        DB::statement('
            UPDATE subscriptions s
            JOIN users u ON s.user_id = u.id
            SET s.organization_id = u.organization_id,
                s.created_by = s.user_id
            WHERE s.organization_id IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'organization_id')) {
                $table->dropForeign(['organization_id']);
                $table->dropIndex(['organization_id']);
                $table->dropColumn('organization_id');
            }
            if (Schema::hasColumn('subscriptions', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
