<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create settings_access table
        Schema::create('settings_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('option_type')->default('platform');
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

        DB::table('settings_access')->insert([
            ["option_type" => "platform", "option_label" => "cPanel", "option_value" => "cpanel", "sort_order" => 1, "is_active" => true, "is_default" => false, "created_at" => now(), "updated_at" => now()],
            ["option_type" => "platform", "option_label" => "FTP", "option_value" => "ftp", "sort_order" => 2, "is_active" => true, "is_default" => false, "created_at" => now(), "updated_at" => now()],
            ["option_type" => "platform", "option_label" => "SSH", "option_value" => "ssh", "sort_order" => 3, "is_active" => true, "is_default" => false, "created_at" => now(), "updated_at" => now()],
            ["option_type" => "platform", "option_label" => "Database", "option_value" => "database", "sort_order" => 4, "is_active" => true, "is_default" => false, "created_at" => now(), "updated_at" => now()],
            ["option_type" => "platform", "option_label" => "Admin Panel", "option_value" => "admin-panel", "sort_order" => 5, "is_active" => true, "is_default" => false, "created_at" => now(), "updated_at" => now()],
            ["option_type" => "platform", "option_label" => "Email", "option_value" => "email", "sort_order" => 6, "is_active" => true, "is_default" => false, "created_at" => now(), "updated_at" => now()],
            ["option_type" => "platform", "option_label" => "Cloud Service", "option_value" => "cloud-service", "sort_order" => 7, "is_active" => true, "is_default" => false, "created_at" => now(), "updated_at" => now()],
            ["option_type" => "platform", "option_label" => "API", "option_value" => "api", "sort_order" => 8, "is_active" => true, "is_default" => false, "created_at" => now(), "updated_at" => now()],
            ["option_type" => "platform", "option_label" => "Other", "option_value" => "other", "sort_order" => 99, "is_active" => true, "is_default" => false, "created_at" => now(), "updated_at" => now()],
        ]);

        // 2. Add subscription statuses to settings_subscription
        DB::table('settings_subscription')->insert([
            ["option_type" => "status", "option_label" => "Active", "option_value" => "active", "color_class" => "green", "sort_order" => 11, "is_active" => true, "is_default" => true, "created_at" => now(), "updated_at" => now()],
            ["option_type" => "status", "option_label" => "Cancelled", "option_value" => "cancelled", "color_class" => "red", "sort_order" => 12, "is_active" => true, "is_default" => false, "created_at" => now(), "updated_at" => now()],
            ["option_type" => "status", "option_label" => "Expired", "option_value" => "expired", "color_class" => "orange", "sort_order" => 13, "is_active" => true, "is_default" => false, "created_at" => now(), "updated_at" => now()],
            ["option_type" => "status", "option_label" => "Pending", "option_value" => "pending", "color_class" => "yellow", "sort_order" => 14, "is_active" => true, "is_default" => false, "created_at" => now(), "updated_at" => now()],
        ]);

        // 3. Add payment methods to settings_financial
        $orgId = DB::table('users')->value('organization_id') ?? 1;
        DB::table('settings_financial')->insert([
            ["organization_id" => $orgId, "option_type" => "payment_method", "option_label" => "Credit Card", "option_value" => "credit-card", "color_class" => "blue", "sort_order" => 21, "created_at" => now(), "updated_at" => now()],
            ["organization_id" => $orgId, "option_type" => "payment_method", "option_label" => "Bank Transfer", "option_value" => "bank-transfer", "color_class" => "green", "sort_order" => 22, "created_at" => now(), "updated_at" => now()],
            ["organization_id" => $orgId, "option_type" => "payment_method", "option_label" => "PayPal", "option_value" => "paypal", "color_class" => "blue", "sort_order" => 23, "created_at" => now(), "updated_at" => now()],
            ["organization_id" => $orgId, "option_type" => "payment_method", "option_label" => "Stripe", "option_value" => "stripe", "color_class" => "purple", "sort_order" => 24, "created_at" => now(), "updated_at" => now()],
            ["organization_id" => $orgId, "option_type" => "payment_method", "option_label" => "Cash", "option_value" => "cash", "color_class" => "green", "sort_order" => 25, "created_at" => now(), "updated_at" => now()],
            ["organization_id" => $orgId, "option_type" => "payment_method", "option_label" => "Check", "option_value" => "check", "color_class" => "slate", "sort_order" => 26, "created_at" => now(), "updated_at" => now()],
            ["organization_id" => $orgId, "option_type" => "payment_method", "option_label" => "Other", "option_value" => "other", "color_class" => "slate", "sort_order" => 99, "created_at" => now(), "updated_at" => now()],
        ]);

        // 4. Drop hierarchical settings tables
        Schema::dropIfExists('setting_options');
        Schema::dropIfExists('setting_groups');
        Schema::dropIfExists('setting_categories');
    }

    public function down(): void
    {
        Schema::dropIfExists('settings_access');
        DB::table('settings_subscription')->where('option_type', 'status')->delete();
        DB::table('settings_financial')->where('option_type', 'payment_method')->delete();
    }
};
