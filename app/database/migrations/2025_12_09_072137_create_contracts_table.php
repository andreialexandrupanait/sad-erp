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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained(); // Original offer
            $table->foreignId('template_id')->nullable()->constrained('document_templates')->nullOnDelete();

            $table->string('contract_number')->unique(); // Contract-SAD-2025-001
            $table->string('title');
            $table->longText('content'); // Full WYSIWYG content with variables replaced

            $table->enum('status', ['draft', 'active', 'completed', 'terminated', 'expired'])->default('draft');
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Null = indefinite
            $table->boolean('auto_renew')->default(false);

            $table->decimal('total_value', 12, 2);
            $table->string('currency', 3)->default('RON');

            $table->string('pdf_path')->nullable(); // Stored PDF
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['client_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
