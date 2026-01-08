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
        // Categories table - groups settings (Domains, Access, Clients, Financial, etc.)
        Schema::create('setting_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Domains", "Access", "Clients"
            $table->string('slug')->unique(); // e.g., "domains", "access"
            $table->string('icon')->nullable(); // SVG icon or icon class
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Setting groups - subgroups within categories (e.g., Domain Registrars, Domain Statuses)
        Schema::create('setting_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('setting_categories')->onDelete('cascade');
            $table->string('name'); // e.g., "Registrars", "Statuses"
            $table->string('slug')->unique(); // e.g., "domain_registrars", "domain_statuses"
            $table->string('key')->unique(); // Machine-readable key for code access
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('has_colors')->default(false); // Whether options support color tags
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Individual options - the actual dropdown values
        Schema::create('setting_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('setting_groups')->onDelete('cascade');
            $table->string('label'); // Display name
            $table->string('value'); // Internal value
            $table->string('color')->nullable(); // Hex color for statuses/tags
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->json('metadata')->nullable(); // Extra data if needed
            $table->timestamps();

            // Ensure unique values within a group
            $table->unique(['group_id', 'value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_options');
        Schema::dropIfExists('setting_groups');
        Schema::dropIfExists('setting_categories');
    }
};
