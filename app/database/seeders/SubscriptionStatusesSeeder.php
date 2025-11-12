<?php

namespace Database\Seeders;

use App\Models\SettingOption;
use Illuminate\Database\Seeder;

class SubscriptionStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'label' => 'Activă',
                'value' => 'activa',
                'color_class' => '#22c55e',
                'sort_order' => 1,
            ],
            [
                'label' => 'Suspendată',
                'value' => 'suspendata',
                'color_class' => '#f59e0b',
                'sort_order' => 2,
            ],
            [
                'label' => 'Anulată',
                'value' => 'anulata',
                'color_class' => '#ef4444',
                'sort_order' => 3,
            ],
        ];

        foreach ($statuses as $status) {
            SettingOption::updateOrCreate(
                [
                    'category' => 'subscription_statuses',
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
