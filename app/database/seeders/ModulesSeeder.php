<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'slug' => 'dashboard',
                'name' => 'Dashboard',
                'description' => 'Main dashboard and overview',
                'icon' => 'home',
                'route_prefix' => 'dashboard',
                'sort_order' => 1,
            ],
            [
                'slug' => 'clients',
                'name' => 'Clients',
                'description' => 'Client management',
                'icon' => 'users',
                'route_prefix' => 'clients',
                'sort_order' => 2,
            ],
            [
                'slug' => 'domains',
                'name' => 'Domains',
                'description' => 'Domain management',
                'icon' => 'globe',
                'route_prefix' => 'domains',
                'sort_order' => 3,
            ],
            [
                'slug' => 'subscriptions',
                'name' => 'Subscriptions',
                'description' => 'Subscription management',
                'icon' => 'refresh-cw',
                'route_prefix' => 'subscriptions',
                'sort_order' => 4,
            ],
            [
                'slug' => 'credentials',
                'name' => 'Credentials',
                'description' => 'Access credentials vault',
                'icon' => 'key',
                'route_prefix' => 'credentials',
                'sort_order' => 5,
            ],
            [
                'slug' => 'finance',
                'name' => 'Finance',
                'description' => 'Financial management (expenses, revenues)',
                'icon' => 'dollar-sign',
                'route_prefix' => 'financial',
                'sort_order' => 6,
            ],
            [
                'slug' => 'internal_accounts',
                'name' => 'Internal Accounts',
                'description' => 'Internal account management',
                'icon' => 'briefcase',
                'route_prefix' => 'internal-accounts',
                'sort_order' => 7,
            ],
            [
                'slug' => 'analytics',
                'name' => 'Analytics',
                'description' => 'Reports and analytics',
                'icon' => 'bar-chart-2',
                'route_prefix' => 'analytics',
                'sort_order' => 8,
            ],
            [
                'slug' => 'settings',
                'name' => 'Settings',
                'description' => 'System settings and configuration',
                'icon' => 'settings',
                'route_prefix' => 'settings',
                'sort_order' => 9,
            ],
        ];

        foreach ($modules as $module) {
            Module::updateOrCreate(
                ['slug' => $module['slug']],
                $module
            );
        }

        $this->command->info('Modules seeded successfully.');
    }
}
