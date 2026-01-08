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
        Schema::create('offer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete(); // Link to predefined service

            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->default('buc'); // buc, ora, luna, proiect
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);

            $table->boolean('is_recurring')->default(false);
            $table->string('billing_cycle')->nullable(); // monthly, yearly, custom
            $table->integer('custom_cycle_days')->nullable();

            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('offer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_items');
    }
};
