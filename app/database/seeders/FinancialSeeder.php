<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use App\Models\Client;
use App\Models\FinancialSetting;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use Carbon\Carbon;

class FinancialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user and organization
        $user = User::first();
        $organization = Organization::first();

        if (!$user || !$organization) {
            $this->command->error('No users or organizations found. Please run user seeders first.');
            return;
        }

        // Temporarily disable global scopes to seed data
        \Illuminate\Support\Facades\Auth::login($user);

        $this->command->info('Seeding financial settings (expense categories)...');

        // Create expense categories
        $categories = [
            ['option_label' => 'Marketing & Advertising', 'option_value' => 'marketing', 'color_class' => 'blue', 'sort_order' => 1],
            ['option_label' => 'Software & Tools', 'option_value' => 'software', 'color_class' => 'purple', 'sort_order' => 2],
            ['option_label' => 'Hosting & Infrastructure', 'option_value' => 'hosting', 'color_class' => 'green', 'sort_order' => 3],
            ['option_label' => 'Office & Supplies', 'option_value' => 'office', 'color_class' => 'yellow', 'sort_order' => 4],
            ['option_label' => 'Professional Services', 'option_value' => 'services', 'color_class' => 'orange', 'sort_order' => 5],
            ['option_label' => 'Salaries & Payroll', 'option_value' => 'salaries', 'color_class' => 'red', 'sort_order' => 6],
            ['option_label' => 'Utilities', 'option_value' => 'utilities', 'color_class' => 'slate', 'sort_order' => 7],
            ['option_label' => 'Other', 'option_value' => 'other', 'color_class' => 'pink', 'sort_order' => 8],
        ];

        foreach ($categories as $category) {
            FinancialSetting::create(array_merge($category, [
                'organization_id' => $organization->id,
                'option_type' => 'expense_category',
            ]));
        }

        $this->command->info('Seeding financial revenues...');

        // Get some clients for revenues
        $clients = Client::take(5)->get();

        // Create sample revenues for current year and last 3 months
        $revenueData = [
            ['month' => -3, 'document_name' => 'Invoice #001 - Web Development', 'amount' => 5000, 'currency' => 'RON'],
            ['month' => -3, 'document_name' => 'Invoice #002 - SEO Services', 'amount' => 1200, 'currency' => 'EUR'],
            ['month' => -2, 'document_name' => 'Invoice #003 - Mobile App', 'amount' => 8500, 'currency' => 'RON'],
            ['month' => -2, 'document_name' => 'Invoice #004 - Consulting', 'amount' => 2000, 'currency' => 'EUR'],
            ['month' => -1, 'document_name' => 'Invoice #005 - Website Redesign', 'amount' => 6200, 'currency' => 'RON'],
            ['month' => -1, 'document_name' => 'Invoice #006 - Maintenance', 'amount' => 800, 'currency' => 'EUR'],
            ['month' => 0, 'document_name' => 'Invoice #007 - E-commerce Platform', 'amount' => 12000, 'currency' => 'RON'],
            ['month' => 0, 'document_name' => 'Invoice #008 - API Integration', 'amount' => 3500, 'currency' => 'EUR'],
        ];

        foreach ($revenueData as $data) {
            $date = Carbon::now()->addMonths($data['month'])->setDay(rand(1, 28));

            FinancialRevenue::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'document_name' => $data['document_name'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'occurred_at' => $date,
                'client_id' => $clients->isNotEmpty() ? $clients->random()->id : null,
                'year' => $date->year,
                'month' => $date->month,
                'note' => 'Sample revenue entry for testing',
            ]);
        }

        $this->command->info('Seeding financial expenses...');

        // Get category IDs
        $categoryIds = FinancialSetting::expenseCategories()->pluck('id')->toArray();

        // Create sample expenses
        $expenseData = [
            ['month' => -3, 'document_name' => 'Google Ads Campaign', 'amount' => 1500, 'currency' => 'RON'],
            ['month' => -3, 'document_name' => 'Adobe Creative Cloud', 'amount' => 60, 'currency' => 'EUR'],
            ['month' => -2, 'document_name' => 'AWS Hosting', 'amount' => 450, 'currency' => 'EUR'],
            ['month' => -2, 'document_name' => 'Office Supplies', 'amount' => 350, 'currency' => 'RON'],
            ['month' => -1, 'document_name' => 'Accountant Services', 'amount' => 800, 'currency' => 'RON'],
            ['month' => -1, 'document_name' => 'GitHub Pro', 'amount' => 45, 'currency' => 'EUR'],
            ['month' => 0, 'document_name' => 'Employee Salary', 'amount' => 5000, 'currency' => 'RON'],
            ['month' => 0, 'document_name' => 'Internet & Phone', 'amount' => 120, 'currency' => 'RON'],
        ];

        foreach ($expenseData as $data) {
            $date = Carbon::now()->addMonths($data['month'])->setDay(rand(1, 28));

            FinancialExpense::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'document_name' => $data['document_name'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'occurred_at' => $date,
                'category_option_id' => !empty($categoryIds) ? $categoryIds[array_rand($categoryIds)] : null,
                'year' => $date->year,
                'month' => $date->month,
                'note' => 'Sample expense entry for testing',
            ]);
        }

        \Illuminate\Support\Facades\Auth::logout();

        $this->command->info('Financial data seeded successfully!');
    }
}
