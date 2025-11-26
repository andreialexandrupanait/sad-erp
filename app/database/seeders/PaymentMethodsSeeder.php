<?php

namespace Database\Seeders;

use App\Models\SettingOption;
use Illuminate\Database\Seeder;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'label' => 'Credit Card',
                'value' => 'credit-card',
                'color_class' => '#3b82f6',
                'sort_order' => 21,
            ],
            [
                'label' => 'Bank Transfer',
                'value' => 'bank-transfer',
                'color_class' => '#22c55e',
                'sort_order' => 22,
            ],
            [
                'label' => 'PayPal',
                'value' => 'paypal',
                'color_class' => '#3b82f6',
                'sort_order' => 23,
            ],
            [
                'label' => 'Stripe',
                'value' => 'stripe',
                'color_class' => '#a855f7',
                'sort_order' => 24,
            ],
            [
                'label' => 'Cash',
                'value' => 'cash',
                'color_class' => '#22c55e',
                'sort_order' => 25,
            ],
            [
                'label' => 'Check',
                'value' => 'check',
                'color_class' => '#64748b',
                'sort_order' => 26,
            ],
            [
                'label' => 'Other',
                'value' => 'other',
                'color_class' => '#64748b',
                'sort_order' => 99,
            ],
        ];

        foreach ($methods as $method) {
            SettingOption::updateOrCreate(
                [
                    'category' => 'payment_methods',
                    'value' => $method['value'],
                ],
                [
                    'label' => $method['label'],
                    'color_class' => $method['color_class'],
                    'sort_order' => $method['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
