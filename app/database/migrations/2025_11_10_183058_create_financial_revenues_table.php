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
        Schema::create('financial_revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('document_name');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('RON');
            $table->date('occurred_at');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('year');
            $table->integer('month');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'user_id']);
            $table->index(['year', 'month']);
            $table->index('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_revenues');
    }
};
