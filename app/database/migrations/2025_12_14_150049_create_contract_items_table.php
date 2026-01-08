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
        Schema::create('contract_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_item_id')->nullable()->constrained('offer_items')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->default('unit');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('total_price', 12, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('contract_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_items');
    }
};
