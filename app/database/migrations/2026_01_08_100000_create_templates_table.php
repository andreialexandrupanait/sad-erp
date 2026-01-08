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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();

            // Type and categorization
            $table->enum('type', ['offer', 'contract', 'annex', 'email']);
            $table->string('category', 100)->default('general');
            $table->string('name', 255);
            $table->string('slug', 255);

            // Content storage (JSON blocks, not raw HTML)
            $table->json('blocks');
            $table->string('schema_version', 10)->default('1.0');

            // Theme/styling configuration
            $table->json('theme')->nullable();

            // Legacy HTML content for migration compatibility
            $table->longText('content')->nullable();
            $table->enum('editor_type', ['tiptap', 'quill', 'legacy'])->default('tiptap');

            // Status flags
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            // Versioning
            $table->unsignedInteger('current_version')->default(1);

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['organization_id', 'type', 'is_active']);
            $table->unique(['organization_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
