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
        Schema::create('contract_annexes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained(); // The offer that created this annex
            $table->foreignId('template_id')->nullable()->constrained('document_templates')->nullOnDelete();

            $table->integer('annex_number'); // 1, 2, 3...
            $table->string('annex_code'); // Annex-SAD-to-CTR-SAD-2025-001-1
            $table->string('title');
            $table->longText('content'); // Full WYSIWYG content

            $table->date('effective_date');
            $table->decimal('additional_value', 12, 2)->default(0);
            $table->string('currency', 3)->default('RON');

            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('contract_id');
            $table->unique(['contract_id', 'annex_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_annexes');
    }
};
