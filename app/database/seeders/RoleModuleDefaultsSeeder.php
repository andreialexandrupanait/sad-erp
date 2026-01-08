<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\RoleModuleDefault;
use Illuminate\Database\Seeder;

class RoleModuleDefaultsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = Module::all()->keyBy('slug');

        if ($modules->isEmpty()) {
            $this->command->error('No modules found. Run ModulesSeeder first.');
            return;
        }

        // Define default permissions for each role
        $roleDefaults = [
            // Admin gets full access to everything
            'admin' => [
                'dashboard' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
                'clients' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
                'domains' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
                'subscriptions' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
                'credentials' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
                'finance' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
                'internal_accounts' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
                'analytics' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
                'settings' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
                'offers' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
                'contracts' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
            ],

            // Manager gets most access but limited settings
            'manager' => [
                'dashboard' => ['view' => true, 'create' => true, 'update' => true, 'delete' => false, 'export' => true],
                'clients' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
                'domains' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
                'subscriptions' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
                'credentials' => ['view' => true, 'create' => true, 'update' => true, 'delete' => false, 'export' => false],
                'finance' => ['view' => true, 'create' => true, 'update' => true, 'delete' => false, 'export' => true],
                'internal_accounts' => ['view' => true, 'create' => true, 'update' => true, 'delete' => false, 'export' => false],
                'analytics' => ['view' => true, 'create' => false, 'update' => false, 'delete' => false, 'export' => true],
                'settings' => ['view' => true, 'create' => false, 'update' => false, 'delete' => false, 'export' => false],
                'offers' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
                'contracts' => ['view' => true, 'create' => true, 'update' => true, 'delete' => true, 'export' => true],
            ],

            // User gets basic access
            'user' => [
                'dashboard' => ['view' => true, 'create' => false, 'update' => false, 'delete' => false, 'export' => false],
                'clients' => ['view' => true, 'create' => true, 'update' => true, 'delete' => false, 'export' => false],
                'domains' => ['view' => true, 'create' => true, 'update' => true, 'delete' => false, 'export' => false],
                'subscriptions' => ['view' => true, 'create' => true, 'update' => true, 'delete' => false, 'export' => false],
                'credentials' => ['view' => true, 'create' => true, 'update' => true, 'delete' => false, 'export' => false],
                'finance' => ['view' => true, 'create' => true, 'update' => true, 'delete' => false, 'export' => false],
                'internal_accounts' => ['view' => true, 'create' => false, 'update' => false, 'delete' => false, 'export' => false],
                'analytics' => ['view' => true, 'create' => false, 'update' => false, 'delete' => false, 'export' => false],
                'settings' => ['view' => false, 'create' => false, 'update' => false, 'delete' => false, 'export' => false],
                'offers' => ['view' => true, 'create' => true, 'update' => true, 'delete' => false, 'export' => false],
                'contracts' => ['view' => true, 'create' => true, 'update' => true, 'delete' => false, 'export' => false],
            ],
        ];

        foreach ($roleDefaults as $role => $modulePermissions) {
            foreach ($modulePermissions as $moduleSlug => $permissions) {
                $module = $modules->get($moduleSlug);

                if (!$module) {
                    $this->command->warn("Module '{$moduleSlug}' not found, skipping.");
                    continue;
                }

                RoleModuleDefault::updateOrCreate(
                    [
                        'role' => $role,
                        'module_id' => $module->id,
                    ],
                    [
                        'can_view' => $permissions['view'],
                        'can_create' => $permissions['create'],
                        'can_update' => $permissions['update'],
                        'can_delete' => $permissions['delete'],
                        'can_export' => $permissions['export'],
                    ]
                );
            }
        }

        // Clear all role caches
        RoleModuleDefault::clearAllCache();

        $this->command->info('Role module defaults seeded successfully.');
    }
}
