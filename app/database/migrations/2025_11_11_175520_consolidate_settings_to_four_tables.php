<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Consolidate 7 settings tables into 4:
     * - settings_app (application/system settings)
     * - settings_options (all dropdown options)
     * - settings_categories (expense categories with hierarchy)
     * - settings_access (access platforms - unchanged)
     */
    public function up(): void
    {
        // 1. Create settings_options table (unified options table)
        Schema::create('settings_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('category'); // client_status, domain_status, subscription_status, billing_cycle, registrar, payment_method
            $table->string('label'); // Display name
            $table->string('value'); // Value used in code
            $table->string('color_class')->nullable(); // For UI styling (statuses)
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'category']);
            $table->index('sort_order');
        });

        // 2. Migrate data from settings_client (old structure)
        $clientStatuses = DB::table('settings_client')->get();
        foreach ($clientStatuses as $status) {
            // Check if old structure (name) or new structure (option_label)
            $label = isset($status->option_label) ? $status->option_label : $status->name;
            $value = isset($status->option_value) ? $status->option_value : \Illuminate\Support\Str::slug($status->name);
            $colorClass = isset($status->color_class) ? $status->color_class : (isset($status->color) ? $status->color : null);
            $sortOrder = isset($status->sort_order) ? $status->sort_order : (isset($status->order_index) ? $status->order_index : 0);
            $organizationId = isset($status->organization_id) ? $status->organization_id : null;

            DB::table('settings_options')->insert([
                'organization_id' => $organizationId,
                'category' => 'client_status',
                'label' => $label,
                'value' => $value,
                'color_class' => $colorClass,
                'sort_order' => $sortOrder,
                'is_active' => $status->is_active,
                'is_default' => isset($status->is_default) ? $status->is_default : false,
                'created_at' => $status->created_at,
                'updated_at' => $status->updated_at,
            ]);
        }

        // 3. Migrate data from settings_domain
        $domainSettings = DB::table('settings_domain')->get();
        foreach ($domainSettings as $setting) {
            $category = $setting->option_type === 'registrar' ? 'domain_registrar' : 'domain_status';
            DB::table('settings_options')->insert([
                'organization_id' => $setting->organization_id,
                'category' => $category,
                'label' => $setting->option_label,
                'value' => $setting->option_value,
                'color_class' => $setting->color_class,
                'sort_order' => $setting->sort_order,
                'is_active' => $setting->is_active,
                'is_default' => $setting->is_default,
                'created_at' => $setting->created_at,
                'updated_at' => $setting->updated_at,
            ]);
        }

        // 4. Migrate data from settings_subscription
        $subscriptionSettings = DB::table('settings_subscription')->get();
        foreach ($subscriptionSettings as $setting) {
            $category = $setting->option_type === 'billing_cycle' ? 'billing_cycle' : 'subscription_status';
            DB::table('settings_options')->insert([
                'organization_id' => $setting->organization_id,
                'category' => $category,
                'label' => $setting->option_label,
                'value' => $setting->option_value,
                'color_class' => $setting->color_class,
                'sort_order' => $setting->sort_order,
                'is_active' => $setting->is_active,
                'is_default' => $setting->is_default,
                'created_at' => $setting->created_at,
                'updated_at' => $setting->updated_at,
            ]);
        }

        // 5. Migrate payment methods from settings_financial
        $paymentMethods = DB::table('settings_financial')->where('option_type', 'payment_method')->get();
        foreach ($paymentMethods as $method) {
            DB::table('settings_options')->insert([
                'organization_id' => $method->organization_id,
                'category' => 'payment_method',
                'label' => $method->option_label,
                'value' => $method->option_value,
                'color_class' => $method->color_class,
                'sort_order' => $method->sort_order,
                'is_active' => true,
                'is_default' => false,
                'created_at' => $method->created_at,
                'updated_at' => $method->updated_at,
            ]);
        }

        // 6. Rename settings_application to settings_app
        Schema::rename('settings_application', 'settings_app');

        // 7. Create settings_categories for expense categories with hierarchy
        Schema::create('settings_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('settings_categories')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('color_class')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'parent_id']);
            $table->index('sort_order');
        });

        // 8. Migrate existing expense categories if any from settings_financial
        $expenseCategories = DB::table('settings_financial')
            ->where('option_type', '!=', 'payment_method')
            ->get();

        foreach ($expenseCategories as $category) {
            DB::table('settings_categories')->insert([
                'organization_id' => $category->organization_id,
                'parent_id' => null,
                'name' => $category->option_label,
                'slug' => $category->option_value,
                'color_class' => $category->color_class,
                'sort_order' => $category->sort_order,
                'is_active' => true,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ]);
        }

        // 9. Drop foreign key constraints first
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
        });

        Schema::table('financial_expenses', function (Blueprint $table) {
            $table->dropForeign(['category_option_id']);
        });

        // 10. Drop old tables
        Schema::dropIfExists('settings_client');
        Schema::dropIfExists('settings_domain');
        Schema::dropIfExists('settings_subscription');
        Schema::dropIfExists('settings_financial');
        Schema::dropIfExists('settings_system');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate old tables structure
        Schema::create('settings_client', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('option_label');
            $table->string('option_value');
            $table->string('color_class')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Restore data from settings_options to old tables
        $clientStatuses = DB::table('settings_options')->where('category', 'client_status')->get();
        foreach ($clientStatuses as $status) {
            DB::table('settings_client')->insert([
                'organization_id' => $status->organization_id,
                'option_label' => $status->label,
                'option_value' => $status->value,
                'color_class' => $status->color_class,
                'sort_order' => $status->sort_order,
                'is_active' => $status->is_active,
                'is_default' => $status->is_default,
                'created_at' => $status->created_at,
                'updated_at' => $status->updated_at,
            ]);
        }

        // Rename settings_app back to settings_application
        Schema::rename('settings_app', 'settings_application');

        // Drop new tables
        Schema::dropIfExists('settings_options');
        Schema::dropIfExists('settings_categories');
    }
};
