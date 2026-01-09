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
        Schema::create('document_files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Polymorphic relationship
            $table->string('documentable_type');
            $table->unsignedBigInteger('documentable_id');

            // Document classification
            $table->enum('category', ['offer', 'contract', 'annex']);
            $table->enum('document_type', ['draft', 'signed']);

            // Versioning
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);

            // File metadata
            $table->string('file_path', 500);
            $table->string('original_filename', 255)->nullable();
            $table->string('mime_type', 100)->default('application/pdf');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_hash', 64)->nullable(); // SHA-256

            // Audit
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Indexes
            $table->index(['documentable_type', 'documentable_id'], 'idx_documentable');
            $table->index(['category', 'created_at'], 'idx_category_year');
            $table->index(['organization_id', 'category'], 'idx_org_category');
            $table->index(['documentable_type', 'documentable_id', 'document_type', 'is_active'], 'idx_active');
            $table->unique(['documentable_type', 'documentable_id', 'document_type', 'version'], 'idx_unique_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_files');
    }
};
