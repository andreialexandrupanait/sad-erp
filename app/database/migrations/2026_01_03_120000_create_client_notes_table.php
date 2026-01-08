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
        Schema::create('client_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // who created the note

            // Content
            $table->text('content');

            // Auto-extracted tags for filtering (stored as JSON array)
            $table->json('tags')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['client_id', 'created_at']);
            $table->index(['organization_id', 'created_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_notes');
    }
};
