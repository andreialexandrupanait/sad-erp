<?php

namespace Database\Seeders;

use App\Models\SettingOption;
use Illuminate\Database\Seeder;

class ExpenseCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Parent categories with their subcategories
        $categories = [
            [
                'label' => 'Afaceri',
                'value' => 'afaceri',
                'color_class' => '#22c55e',
                'sort_order' => 1,
                'children' => [],
            ],
            [
                'label' => 'Auto',
                'value' => 'auto',
                'color_class' => '#a855f7',
                'sort_order' => 2,
                'children' => [
                    ['label' => 'Combustibil', 'value' => 'combustibil', 'color_class' => '#1e40af', 'sort_order' => 1],
                    ['label' => 'Casa', 'value' => 'casa', 'color_class' => '#1e40af', 'sort_order' => 2],
                    ['label' => 'RCA', 'value' => 'rca', 'color_class' => '#1e40af', 'sort_order' => 3],
                    ['label' => 'Rovinieta', 'value' => 'rovinieta', 'color_class' => '#1e40af', 'sort_order' => 4],
                    ['label' => 'Reparatii auto', 'value' => 'reparatii-auto', 'color_class' => '#1e40af', 'sort_order' => 5],
                    ['label' => 'Leasing', 'value' => 'leasing', 'color_class' => '#1e40af', 'sort_order' => 6],
                ],
            ],
            [
                'label' => 'Consumabile',
                'value' => 'consumabile',
                'color_class' => '#06b6d4',
                'sort_order' => 3,
                'children' => [],
            ],
            [
                'label' => 'Banca',
                'value' => 'banca',
                'color_class' => '#1e40af',
                'sort_order' => 4,
                'children' => [],
            ],
            [
                'label' => 'Impozite si taxe',
                'value' => 'impozite-si-taxe',
                'color_class' => '#f59e0b',
                'sort_order' => 5,
                'children' => [],
            ],
            [
                'label' => 'Protocol',
                'value' => 'protocol',
                'color_class' => '#a855f7',
                'sort_order' => 6,
                'children' => [
                    ['label' => 'Masa la restaurant', 'value' => 'masa-la-restaurant', 'color_class' => '#1e40af', 'sort_order' => 1],
                ],
            ],
            [
                'label' => 'Abonamente',
                'value' => 'abonamente',
                'color_class' => '#3b82f6',
                'sort_order' => 7,
                'children' => [
                    ['label' => 'Postmark', 'value' => 'postmark', 'color_class' => '#1e40af', 'sort_order' => 1],
                    ['label' => 'Chat GPT', 'value' => 'chat-gpt', 'color_class' => '#1e40af', 'sort_order' => 2],
                    ['label' => 'Google Suite', 'value' => 'google-suite', 'color_class' => '#1e40af', 'sort_order' => 3],
                ],
            ],
            [
                'label' => 'IT & Software',
                'value' => 'it-software',
                'color_class' => '#22c55e',
                'sort_order' => 8,
                'children' => [
                    ['label' => 'Gazduire web', 'value' => 'gazduire-web', 'color_class' => '#1e40af', 'sort_order' => 1],
                ],
            ],
            [
                'label' => 'Birou',
                'value' => 'birou',
                'color_class' => '#22c55e',
                'sort_order' => 9,
                'children' => [
                    ['label' => 'Papetarie', 'value' => 'papetarie', 'color_class' => '#1e40af', 'sort_order' => 1],
                ],
            ],
            [
                'label' => 'Resurse umane',
                'value' => 'resurse-umane',
                'color_class' => '#ef4444',
                'sort_order' => 10,
                'children' => [
                    ['label' => 'Salarii', 'value' => 'salarii', 'color_class' => '#1e40af', 'sort_order' => 1],
                    ['label' => 'Tichete', 'value' => 'tichete', 'color_class' => '#1e40af', 'sort_order' => 2],
                    ['label' => 'Contributii salariale', 'value' => 'contributii-salariale', 'color_class' => '#1e40af', 'sort_order' => 3],
                ],
            ],
            [
                'label' => 'Domenii web',
                'value' => 'domenii-web',
                'color_class' => '#22c55e',
                'sort_order' => 11,
                'children' => [],
            ],
        ];

        foreach ($categories as $categoryData) {
            // Create or update parent category
            $parent = SettingOption::updateOrCreate(
                [
                    'category' => 'expense_categories',
                    'value' => $categoryData['value'],
                ],
                [
                    'label' => $categoryData['label'],
                    'color_class' => $categoryData['color_class'],
                    'sort_order' => $categoryData['sort_order'],
                    'parent_id' => null,
                    'is_active' => true,
                ]
            );

            // Create or update subcategories
            foreach ($categoryData['children'] as $childData) {
                SettingOption::updateOrCreate(
                    [
                        'category' => 'expense_categories',
                        'value' => $childData['value'],
                    ],
                    [
                        'label' => $childData['label'],
                        'color_class' => $childData['color_class'],
                        'sort_order' => $childData['sort_order'],
                        'parent_id' => $parent->id,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
