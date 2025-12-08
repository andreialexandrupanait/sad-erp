<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Consolidate settings_access and settings_categories into settings_options
     * This creates a single unified table for all dropdown options
     */
    public function up(): void
    {
        // 1. Migrate access platforms from settings_access to settings_options
        $accessPlatforms = DB::table('settings_access')
            ->where('option_type', 'platform')
            ->get();

        foreach ($accessPlatforms as $platform) {
            DB::table('settings_options')->insert([
                'organization_id' => $platform->organization_id,
                'category' => 'access_platforms',
                'label' => $platform->option_label,
                'value' => $platform->option_value,
                'color_class' => $platform->color_class,
                'sort_order' => $platform->sort_order,
                'is_active' => $platform->is_active,
                'is_default' => $platform->is_default,
                'created_at' => $platform->created_at,
                'updated_at' => $platform->updated_at,
                'deleted_at' => $platform->deleted_at,
            ]);
        }

        // 2. Migrate expense categories from settings_categories to settings_options (if any exist)
        $categories = DB::table('settings_categories')->get();

        foreach ($categories as $category) {
            DB::table('settings_options')->insert([
                'organization_id' => $category->organization_id,
                'category' => 'expense_categories',
                'label' => $category->name,
                'value' => $category->slug,
                'color_class' => $category->color_class,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active,
                'is_default' => false,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
                'deleted_at' => $category->deleted_at ?? null,
            ]);
        }

        // 3. Drop the old tables
        Schema::dropIfExists('settings_access');
        Schema::dropIfExists('settings_categories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate settings_access table
        Schema::create('settings_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('option_type', 50)->index();
            $table->string('option_label', 255);
            $table->string('option_value', 255);
            $table->string('color_class', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'option_type']);
        });

        // Recreate settings_categories table
        Schema::create('settings_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('settings_categories')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->string('color_class', 50)->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
        });

        // Restore data from settings_options
        $accessPlatforms = DB::table('settings_options')
            ->where('category', 'access_platforms')
            ->get();

        foreach ($accessPlatforms as $platform) {
            DB::table('settings_access')->insert([
                'id' => $platform->id,
                'organization_id' => $platform->organization_id,
                'option_type' => 'platform',
                'option_label' => $platform->label,
                'option_value' => $platform->value,
                'color_class' => $platform->color_class,
                'sort_order' => $platform->sort_order,
                'is_active' => $platform->is_active,
                'is_default' => $platform->is_default,
                'created_at' => $platform->created_at,
                'updated_at' => $platform->updated_at,
                'deleted_at' => $platform->deleted_at,
            ]);
        }

        $expenseCategories = DB::table('settings_options')
            ->where('category', 'expense_categories')
            ->get();

        foreach ($expenseCategories as $category) {
            DB::table('settings_categories')->insert([
                'id' => $category->id,
                'organization_id' => $category->organization_id,
                'parent_id' => null,
                'name' => $category->label,
                'slug' => $category->value,
                'color_class' => $category->color_class,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
                'deleted_at' => $category->deleted_at,
            ]);
        }

        // Remove migrated records from settings_options
        DB::table('settings_options')->where('category', 'access_platforms')->delete();
        DB::table('settings_options')->where('category', 'expense_categories')->delete();
    }
};
