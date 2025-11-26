<?php

namespace Database\Seeders;

use App\Models\SettingOption;
use Illuminate\Database\Seeder;

class DashboardQuickActionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actions = [
            [
                'label' => 'Client',
                'value' => 'client-create',
                'color_class' => '#3b82f6', // Blue-600
                'sort_order' => 1,
            ],
            [
                'label' => 'Venit',
                'value' => 'revenue-create',
                'color_class' => '#059669', // Emerald-600
                'sort_order' => 2,
            ],
            [
                'label' => 'Cheltuială',
                'value' => 'expense-create',
                'color_class' => '#e11d48', // Rose-600
                'sort_order' => 3,
            ],
            [
                'label' => 'Abonament',
                'value' => 'subscription-create',
                'color_class' => '#ffffff', // White (outlined)
                'sort_order' => 4,
            ],
            [
                'label' => 'Acces',
                'value' => 'credential-create',
                'color_class' => '#ffffff', // White (outlined)
                'sort_order' => 5,
            ],
            [
                'label' => 'Domeniu',
                'value' => 'domain-create',
                'color_class' => '#ffffff', // White (outlined)
                'sort_order' => 6,
            ],
        ];

        foreach ($actions as $action) {
            SettingOption::updateOrCreate(
                [
                    'category' => 'dashboard_quick_actions',
                    'value' => $action['value'],
                ],
                [
                    'label' => $action['label'],
                    'color_class' => $action['color_class'],
                    'sort_order' => $action['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
