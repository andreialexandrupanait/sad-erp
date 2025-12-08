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
        Schema::table('clients', function (Blueprint $table) {
            // Add organization_id for organization-level scoping
            $table->foreignId('organization_id')->nullable()->after('id')->constrained()->nullOnDelete();

            // Add created_by to track who created the record (for audit, not access control)
            $table->foreignId('created_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();

            // Add index for organization queries
            $table->index('organization_id');
        });

        // Migrate existing data: set organization_id based on user's organization
        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite doesn't support UPDATE ... JOIN syntax
            DB::statement('
                UPDATE clients
                SET organization_id = (SELECT organization_id FROM users WHERE users.id = clients.user_id),
                    created_by = user_id
                WHERE organization_id IS NULL
            ');
        } else {
            // MySQL
            DB::statement('
                UPDATE clients c
                JOIN users u ON c.user_id = u.id
                SET c.organization_id = u.organization_id,
                    c.created_by = c.user_id
                WHERE c.organization_id IS NULL
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['organization_id', 'created_by']);
        });
    }
};
