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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Creator/Owner

            $table->string('vendor_name'); // Nume furnizor (ex: Adobe, Microsoft)
            $table->decimal('price', 10, 2); // Pret in RON
            $table->enum('billing_cycle', ['monthly', 'annual', 'custom'])->default('monthly');
            $table->integer('custom_days')->nullable(); // For 'custom' billing cycle

            $table->date('start_date'); // Data inceput abonament
            $table->date('next_renewal_date'); // Data urmatorului renewal

            $table->enum('status', ['active', 'paused', 'cancelled'])->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['organization_id', 'user_id']);
            $table->index(['organization_id', 'status']);
            $table->index('next_renewal_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
