<?php

namespace Database\Seeders;

use App\Models\SettingOption;
use Illuminate\Database\Seeder;

class ClientStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'label' => 'Mentenanta',
                'value' => 'mentenanta',
                'color_class' => '#06b6d4',
                'sort_order' => 1,
            ],
            [
                'label' => 'In progress',
                'value' => 'in-progress',
                'color_class' => '#f59e0b',
                'sort_order' => 2,
            ],
            [
                'label' => 'Supraveghere',
                'value' => 'supraveghere',
                'color_class' => '#a855f7',
                'sort_order' => 3,
            ],
            [
                'label' => 'On hold',
                'value' => 'on-hold',
                'color_class' => '#94a3b8',
                'sort_order' => 4,
            ],
            [
                'label' => 'Completed',
                'value' => 'completed',
                'color_class' => '#22c55e',
                'sort_order' => 5,
            ],
            [
                'label' => 'Canceled',
                'value' => 'canceled',
                'color_class' => '#000000',
                'sort_order' => 6,
            ],
        ];

        foreach ($statuses as $status) {
            SettingOption::updateOrCreate(
                [
                    'category' => 'client_statuses',
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
