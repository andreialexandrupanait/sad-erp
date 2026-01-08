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
        Schema::create('annexes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->string('annex_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['annex', 'amendment'])->default('annex');
            $table->integer('version')->default(1);
            $table->date('signed_date')->nullable();
            $table->string('pdf_path')->nullable();
            $table->text('changes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'contract_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annexes');
    }
};
