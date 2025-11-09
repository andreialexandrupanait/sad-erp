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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->string('domain_name')->unique();
            $table->string('registrar')->nullable();
            $table->enum('status', ['Active', 'Expiring', 'Expired', 'Suspended'])->default('Active');
            $table->date('registration_date')->nullable();
            $table->date('expiry_date');
            $table->decimal('annual_cost', 10, 2)->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'expiry_date']);
            $table->index(['organization_id', 'client_id']);
            $table->index('domain_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
