<?php

namespace Database\Seeders;

use App\Models\SettingOption;
use Illuminate\Database\Seeder;

class DomainStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'label' => 'Activ',
                'value' => 'activ',
                'color_class' => '#22c55e',
                'sort_order' => 1,
            ],
            [
                'label' => 'Expirat',
                'value' => 'expirat',
                'color_class' => '#ef4444',
                'sort_order' => 2,
            ],
            [
                'label' => 'Vandut',
                'value' => 'vandut',
                'color_class' => '#3b82f6',
                'sort_order' => 3,
            ],
        ];

        foreach ($statuses as $status) {
            SettingOption::updateOrCreate(
                [
                    'category' => 'domain_statuses',
                    'value' => $status['value'],
                ],
                [
                    'label' => $status['label'],
                    'color_class' => $status['color_class'],
                    'sort_order' => $status['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
