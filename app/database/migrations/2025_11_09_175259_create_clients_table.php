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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // owner_user
            $table->foreignId('status_id')->nullable()->constrained('client_settings')->onDelete('set null');

            // Core identification
            $table->string('name'); // Display name (required)
            $table->string('company_name')->nullable(); // Official company name
            $table->string('slug')->unique(); // URL-friendly identifier
            $table->string('tax_id')->nullable(); // CUI / Tax ID
            $table->string('registration_number')->nullable(); // Company registration number

            // Contact information
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();

            // VAT information
            $table->boolean('vat_payer')->default(false);

            // Additional fields
            $table->text('notes')->nullable();
            $table->integer('order_index')->default(0); // Manual ordering for UI

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['user_id', 'status_id']);
            $table->index('order_index');
            $table->unique(['tax_id', 'user_id']); // Unique CUI per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
