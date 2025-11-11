<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Create settings_subscription table for billing cycle options
     */
    public function up(): void
    {
        Schema::create('settings_subscription', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('option_type')->default('billing_cycle'); // billing_cycle, status, etc.
            $table->string('option_label'); // Display name
            $table->string('option_value'); // Value used in code
            $table->string('color_class')->nullable(); // For UI styling
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'option_type']);
            $table->index('sort_order');
        });

        // Seed initial billing cycle options
        DB::table('settings_subscription')->insert([
            [
                'option_type' => 'billing_cycle',
                'option_label' => 'Saptamanal',
                'option_value' => 'saptamanal',
                'sort_order' => 1,
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'option_type' => 'billing_cycle',
                'option_label' => 'Lunar',
                'option_value' => 'lunar',
                'sort_order' => 2,
                'is_active' => true,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'option_type' => 'billing_cycle',
                'option_label' => 'Anual',
                'option_value' => 'anual',
                'sort_order' => 3,
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'option_type' => 'billing_cycle',
                'option_label' => 'Custom',
                'option_value' => 'custom',
                'sort_order' => 4,
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings_subscription');
    }
};
