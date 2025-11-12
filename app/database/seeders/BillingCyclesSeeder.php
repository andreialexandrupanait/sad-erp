<?php

namespace Database\Seeders;

use App\Models\SettingOption;
use Illuminate\Database\Seeder;

class BillingCyclesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cycles = [
            [
                'label' => 'Saptamanal',
                'value' => 'saptamanal',
                'sort_order' => 1,
            ],
            [
                'label' => 'Lunar',
                'value' => 'lunar',
                'sort_order' => 2,
            ],
            [
                'label' => 'Anual',
                'value' => 'anual',
                'sort_order' => 3,
            ],
            [
                'label' => 'Custom',
                'value' => 'custom',
                'sort_order' => 4,
            ],
        ];

        foreach ($cycles as $cycle) {
            SettingOption::updateOrCreate(
                [
                    'category' => 'billing_cycles',
                    'value' => $cycle['value'],
                ],
                [
                    'label' => $cycle['label'],
                    'sort_order' => $cycle['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
