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
        Schema::create('internal_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Creator
            $table->string('nume_cont_aplicatie'); // Account/Application name
            $table->string('platforma'); // Platform type
            $table->string('url')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable(); // Encrypted
            $table->boolean('accesibil_echipei')->default(false); // Team accessible
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'user_id']);
            $table->index(['organization_id', 'accesibil_echipei']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_accounts');
    }
};
