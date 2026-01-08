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
        if (!Schema::hasTable('offer_templates')) {
            Schema::create('offer_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->json('blocks')->nullable();
                $table->json('theme')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['organization_id', 'is_active']);
            });
        }

        // Add editor_version to offers table for backward compatibility
        if (!Schema::hasColumn('offers', 'editor_version')) {
            Schema::table('offers', function (Blueprint $table) {
                $table->string('editor_version')->nullable()->after('blocks');
            });
        }

        // Add offer_template_id reference (using different name to avoid conflict with existing template_id)
        if (!Schema::hasColumn('offers', 'offer_template_id')) {
            Schema::table('offers', function (Blueprint $table) {
                $table->foreignId('offer_template_id')->nullable()
                      ->constrained('offer_templates')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('offers', 'offer_template_id')) {
            Schema::table('offers', function (Blueprint $table) {
                $table->dropForeign(['offer_template_id']);
                $table->dropColumn('offer_template_id');
            });
        }

        if (Schema::hasColumn('offers', 'editor_version')) {
            Schema::table('offers', function (Blueprint $table) {
                $table->dropColumn('editor_version');
            });
        }

        Schema::dropIfExists('offer_templates');
    }
};
