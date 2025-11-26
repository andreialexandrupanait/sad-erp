<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Create settings_domain table for domain registrars and statuses
     */
    public function up(): void
    {
        Schema::create('settings_domain', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('option_type'); // registrar, status
            $table->string('option_label');
            $table->string('option_value');
            $table->string('color_class')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'option_type']);
            $table->index('sort_order');
        });

        // Migrate registrars from hierarchical settings
        DB::table('settings_domain')->insert([
            ['option_type' => 'registrar', 'option_label' => 'Google Domains', 'option_value' => 'google-domains', 'color_class' => null, 'sort_order' => 1, 'is_active' => true, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
            ['option_type' => 'registrar', 'option_label' => 'Cloudflare', 'option_value' => 'cloudflare', 'color_class' => null, 'sort_order' => 2, 'is_active' => true, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
            ['option_type' => 'registrar', 'option_label' => 'GoDaddy', 'option_value' => 'godaddy', 'color_class' => null, 'sort_order' => 3, 'is_active' => true, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
            ['option_type' => 'registrar', 'option_label' => 'Name.com', 'option_value' => 'name-com', 'color_class' => null, 'sort_order' => 4, 'is_active' => true, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
            ['option_type' => 'registrar', 'option_label' => 'Hover', 'option_value' => 'hover', 'color_class' => null, 'sort_order' => 5, 'is_active' => true, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
            ['option_type' => 'registrar', 'option_label' => 'Other', 'option_value' => 'other', 'color_class' => null, 'sort_order' => 6, 'is_active' => true, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Migrate statuses from hierarchical settings
        DB::table('settings_domain')->insert([
            ['option_type' => 'status', 'option_label' => 'Active', 'option_value' => 'active', 'color_class' => 'green', 'sort_order' => 1, 'is_active' => true, 'is_default' => true, 'created_at' => now(), 'updated_at' => now()],
            ['option_type' => 'status', 'option_label' => 'Pending', 'option_value' => 'pending', 'color_class' => 'yellow', 'sort_order' => 2, 'is_active' => true, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
            ['option_type' => 'status', 'option_label' => 'Expired', 'option_value' => 'expired', 'color_class' => 'red', 'sort_order' => 3, 'is_active' => true, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
            ['option_type' => 'status', 'option_label' => 'Suspended', 'option_value' => 'suspended', 'color_class' => 'orange', 'sort_order' => 4, 'is_active' => true, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
            ['option_type' => 'status', 'option_label' => 'Cancelled', 'option_value' => 'cancelled', 'color_class' => 'slate', 'sort_order' => 5, 'is_active' => true, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings_domain');
    }
};
