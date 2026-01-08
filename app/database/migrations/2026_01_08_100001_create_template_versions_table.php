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
        Schema::create('template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->cascadeOnDelete();

            // Version info
            $table->unsignedInteger('version_number');

            // Snapshot of content at this version
            $table->json('blocks');
            $table->json('theme')->nullable();

            // Content hash for change detection
            $table->string('content_hash', 64);

            // Metadata
            $table->string('reason', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('created_at');

            // Indexes
            $table->index(['template_id', 'version_number']);
            $table->unique(['template_id', 'version_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_versions');
    }
};
