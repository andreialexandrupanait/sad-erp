<?php

namespace Database\Seeders;

use App\Models\SettingOption;
use Illuminate\Database\Seeder;

class CurrenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'label' => 'RON',
                'value' => 'RON',
                'color_class' => '#3b82f6',
                'sort_order' => 1,
            ],
            [
                'label' => 'EUR',
                'value' => 'EUR',
                'color_class' => '#10b981',
                'sort_order' => 2,
            ],
            [
                'label' => 'USD',
                'value' => 'USD',
                'color_class' => '#f59e0b',
                'sort_order' => 3,
            ],
            [
                'label' => 'GBP',
                'value' => 'GBP',
                'color_class' => '#8b5cf6',
                'sort_order' => 4,
            ],
        ];

        foreach ($currencies as $currency) {
            SettingOption::updateOrCreate(
                [
                    'category' => 'currencies',
                    'value' => $currency['value'],
                ],
                [
                    'label' => $currency['label'],
                    'color_class' => $currency['color_class'],
                    'sort_order' => $currency['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
