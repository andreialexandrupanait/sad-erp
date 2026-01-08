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
        Schema::create('clickup_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type'); // 'user', 'status', 'priority', 'list', 'task', 'space', 'folder', 'tag'
            $table->string('clickup_id');
            $table->unsignedBigInteger('laravel_id');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'entity_type', 'clickup_id'], 'clickup_mapping_unique');
            $table->index(['entity_type', 'clickup_id']);
            $table->index(['entity_type', 'laravel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clickup_mappings');
    }
};
