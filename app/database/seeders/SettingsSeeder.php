<?php

namespace Database\Seeders;

use App\Models\SettingCategory;
use App\Models\SettingGroup;
use App\Models\SettingOption;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Domain Settings
        $domains = SettingCategory::create([
            'name' => 'Domains',
            'slug' => 'domains',
            'icon' => 'globe',
            'description' => 'Domain management settings',
            'order' => 1,
        ]);

        $registrarsGroup = SettingGroup::create([
            'category_id' => $domains->id,
            'name' => 'Registrars',
            'slug' => 'domain-registrars',
            'key' => 'domain_registrars',
            'description' => 'Domain name registrars',
            'order' => 1,
        ]);

        $registrars = [
            ['label' => 'GoDaddy', 'value' => 'godaddy', 'order' => 1],
            ['label' => 'Namecheap', 'value' => 'namecheap', 'order' => 2],
            ['label' => 'Google Domains', 'value' => 'google-domains', 'order' => 3],
            ['label' => 'Cloudflare', 'value' => 'cloudflare', 'order' => 4],
            ['label' => 'Name.com', 'value' => 'name-com', 'order' => 5],
            ['label' => 'Hover', 'value' => 'hover', 'order' => 6],
            ['label' => 'Other', 'value' => 'other', 'order' => 99],
        ];

        foreach ($registrars as $registrar) {
            SettingOption::create(array_merge(['group_id' => $registrarsGroup->id], $registrar));
        }

        $domainStatusGroup = SettingGroup::create([
            'category_id' => $domains->id,
            'name' => 'Statuses',
            'slug' => 'domain-statuses',
            'key' => 'domain_statuses',
            'description' => 'Domain status options',
            'order' => 2,
            'has_colors' => true,
        ]);

        $domainStatuses = [
            ['label' => 'Active', 'value' => 'active', 'color' => '#10b981', 'order' => 1, 'is_default' => true],
            ['label' => 'Pending', 'value' => 'pending', 'color' => '#f59e0b', 'order' => 2],
            ['label' => 'Expired', 'value' => 'expired', 'color' => '#ef4444', 'order' => 3],
            ['label' => 'Suspended', 'value' => 'suspended', 'color' => '#8b5cf6', 'order' => 4],
            ['label' => 'Cancelled', 'value' => 'cancelled', 'color' => '#6b7280', 'order' => 5],
        ];

        foreach ($domainStatuses as $status) {
            SettingOption::create(array_merge(['group_id' => $domainStatusGroup->id], $status));
        }

        // Access/Credentials Settings
        $access = SettingCategory::create([
            'name' => 'Access',
            'slug' => 'access',
            'icon' => 'key',
            'description' => 'Credentials and access management settings',
            'order' => 2,
        ]);

        $platformsGroup = SettingGroup::create([
            'category_id' => $access->id,
            'name' => 'Platforms',
            'slug' => 'access-platforms',
            'key' => 'access_platforms',
            'description' => 'Platform/service types',
            'order' => 1,
        ]);

        $platforms = [
            ['label' => 'cPanel', 'value' => 'cpanel', 'order' => 1],
            ['label' => 'FTP', 'value' => 'ftp', 'order' => 2],
            ['label' => 'SSH', 'value' => 'ssh', 'order' => 3],
            ['label' => 'Database', 'value' => 'database', 'order' => 4],
            ['label' => 'Admin Panel', 'value' => 'admin-panel', 'order' => 5],
            ['label' => 'Email', 'value' => 'email', 'order' => 6],
            ['label' => 'Cloud Service', 'value' => 'cloud-service', 'order' => 7],
            ['label' => 'API', 'value' => 'api', 'order' => 8],
            ['label' => 'Other', 'value' => 'other', 'order' => 99],
        ];

        foreach ($platforms as $platform) {
            SettingOption::create(array_merge(['group_id' => $platformsGroup->id], $platform));
        }

        // Client Settings
        $clients = SettingCategory::create([
            'name' => 'Clients',
            'slug' => 'clients',
            'icon' => 'users',
            'description' => 'Client management settings',
            'order' => 3,
        ]);

        $clientStatusGroup = SettingGroup::create([
            'category_id' => $clients->id,
            'name' => 'Statuses',
            'slug' => 'client-statuses',
            'key' => 'client_statuses',
            'description' => 'Client status options',
            'order' => 1,
            'has_colors' => true,
        ]);

        $clientStatuses = [
            ['label' => 'Active', 'value' => 'active', 'color' => '#10b981', 'order' => 1, 'is_default' => true],
            ['label' => 'Inactive', 'value' => 'inactive', 'color' => '#6b7280', 'order' => 2],
            ['label' => 'Prospect', 'value' => 'prospect', 'color' => '#3b82f6', 'order' => 3],
            ['label' => 'Suspended', 'value' => 'suspended', 'color' => '#f59e0b', 'order' => 4],
        ];

        foreach ($clientStatuses as $status) {
            SettingOption::create(array_merge(['group_id' => $clientStatusGroup->id], $status));
        }

        // Financial Settings
        $financial = SettingCategory::create([
            'name' => 'Financial',
            'slug' => 'financial',
            'icon' => 'dollar-sign',
            'description' => 'Financial and accounting settings',
            'order' => 4,
        ]);

        $expenseCategoryGroup = SettingGroup::create([
            'category_id' => $financial->id,
            'name' => 'Expense Categories',
            'slug' => 'expense-categories',
            'key' => 'expense_categories',
            'description' => 'Categories for expenses',
            'order' => 1,
        ]);

        $expenseCategories = [
            ['label' => 'Software Licenses', 'value' => 'software-licenses', 'order' => 1],
            ['label' => 'Hosting & Servers', 'value' => 'hosting-servers', 'order' => 2],
            ['label' => 'Domain Registrations', 'value' => 'domain-registrations', 'order' => 3],
            ['label' => 'Marketing & Advertising', 'value' => 'marketing-advertising', 'order' => 4],
            ['label' => 'Professional Services', 'value' => 'professional-services', 'order' => 5],
            ['label' => 'Office Supplies', 'value' => 'office-supplies', 'order' => 6],
            ['label' => 'Other', 'value' => 'other', 'order' => 99],
        ];

        foreach ($expenseCategories as $category) {
            SettingOption::create(array_merge(['group_id' => $expenseCategoryGroup->id], $category));
        }

        $paymentMethodGroup = SettingGroup::create([
            'category_id' => $financial->id,
            'name' => 'Payment Methods',
            'slug' => 'payment-methods',
            'key' => 'payment_methods',
            'description' => 'Available payment methods',
            'order' => 2,
        ]);

        $paymentMethods = [
            ['label' => 'Credit Card', 'value' => 'credit-card', 'order' => 1],
            ['label' => 'Bank Transfer', 'value' => 'bank-transfer', 'order' => 2],
            ['label' => 'PayPal', 'value' => 'paypal', 'order' => 3],
            ['label' => 'Stripe', 'value' => 'stripe', 'order' => 4],
            ['label' => 'Cash', 'value' => 'cash', 'order' => 5],
            ['label' => 'Check', 'value' => 'check', 'order' => 6],
            ['label' => 'Other', 'value' => 'other', 'order' => 99],
        ];

        foreach ($paymentMethods as $method) {
            SettingOption::create(array_merge(['group_id' => $paymentMethodGroup->id], $method));
        }

        // Subscription Settings
        $subscriptions = SettingCategory::create([
            'name' => 'Subscriptions',
            'slug' => 'subscriptions',
            'icon' => 'repeat',
            'description' => 'Subscription management settings',
            'order' => 5,
        ]);

        $subscriptionStatusGroup = SettingGroup::create([
            'category_id' => $subscriptions->id,
            'name' => 'Statuses',
            'slug' => 'subscription-statuses',
            'key' => 'subscription_statuses',
            'description' => 'Subscription status options',
            'order' => 1,
            'has_colors' => true,
        ]);

        $subscriptionStatuses = [
            ['label' => 'Active', 'value' => 'active', 'color' => '#10b981', 'order' => 1, 'is_default' => true],
            ['label' => 'Cancelled', 'value' => 'cancelled', 'color' => '#6b7280', 'order' => 2],
            ['label' => 'Expired', 'value' => 'expired', 'color' => '#ef4444', 'order' => 3],
            ['label' => 'Pending', 'value' => 'pending', 'color' => '#f59e0b', 'order' => 4],
        ];

        foreach ($subscriptionStatuses as $status) {
            SettingOption::create(array_merge(['group_id' => $subscriptionStatusGroup->id], $status));
        }

        $billingCycleGroup = SettingGroup::create([
            'category_id' => $subscriptions->id,
            'name' => 'Billing Cycles',
            'slug' => 'billing-cycles',
            'key' => 'billing_cycles',
            'description' => 'Subscription billing cycle options',
            'order' => 2,
        ]);

        $billingCycles = [
            ['label' => 'Monthly', 'value' => 'monthly', 'order' => 1, 'is_default' => true],
            ['label' => 'Quarterly', 'value' => 'quarterly', 'order' => 2],
            ['label' => 'Semi-Annual', 'value' => 'semi-annual', 'order' => 3],
            ['label' => 'Annual', 'value' => 'annual', 'order' => 4],
            ['label' => 'Biennial', 'value' => 'biennial', 'order' => 5],
            ['label' => 'One-Time', 'value' => 'one-time', 'order' => 6],
        ];

        foreach ($billingCycles as $cycle) {
            SettingOption::create(array_merge(['group_id' => $billingCycleGroup->id], $cycle));
        }
    }
}
