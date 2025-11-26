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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('task_lists')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // creator
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('task_services')->nullOnDelete();
            $table->foreignId('status_id')->constrained('settings_options')->restrictOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();

            // Time tracking in minutes
            $table->integer('time_tracked')->default(0)->comment('Time in minutes');

            // Billing
            $table->decimal('amount', 10, 2)->nullable()->comment('Hourly rate used');
            $table->decimal('total_amount', 10, 2)->default(0)->comment('Calculated: (time_tracked / 60) * amount');

            $table->integer('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['list_id', 'organization_id', 'user_id']);
            $table->index(['assigned_to', 'status_id']);
            $table->index('service_id');
            $table->index('due_date');
            $table->index('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
