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
        Schema::create('smartbill_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->json('options')->nullable(); // Import options (download_pdfs, dry_run, etc.)
            $table->json('stats')->nullable(); // Import statistics (imported, skipped, errors)
            $table->json('errors')->nullable(); // List of error messages
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smartbill_imports');
    }
};
