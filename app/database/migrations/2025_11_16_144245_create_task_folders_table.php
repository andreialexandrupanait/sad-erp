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
        Schema::create('task_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_id')->constrained('task_spaces')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('color')->nullable()->default('#8b5cf6');
            $table->integer('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['space_id', 'organization_id', 'user_id']);
            $table->index('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_folders');
    }
};
