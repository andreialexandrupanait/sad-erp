<?php

namespace Database\Seeders;

use App\Models\SettingOption;
use Illuminate\Database\Seeder;

class DomainRegistrarsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $registrars = [
            [
                'label' => 'Fără registrar',
                'value' => 'fara-registrar',
                'sort_order' => 1,
            ],
            [
                'label' => 'Romarg',
                'value' => 'romarg',
                'sort_order' => 2,
            ],
            [
                'label' => 'Rotld',
                'value' => 'rotld',
                'sort_order' => 3,
            ],
            [
                'label' => 'Simplenet',
                'value' => 'simplenet',
                'sort_order' => 4,
            ],
            [
                'label' => 'Name Bright',
                'value' => 'name-bright',
                'sort_order' => 5,
            ],
            [
                'label' => 'Gazduire Net',
                'value' => 'gazduire-net',
                'sort_order' => 6,
            ],
            [
                'label' => 'Tucows',
                'value' => 'tucows',
                'sort_order' => 7,
            ],
            [
                'label' => 'HostVision',
                'value' => 'hostvision',
                'sort_order' => 8,
            ],
        ];

        foreach ($registrars as $registrar) {
            SettingOption::updateOrCreate(
                [
                    'category' => 'domain_registrars',
                    'value' => $registrar['value'],
                ],
                [
                    'label' => $registrar['label'],
                    'sort_order' => $registrar['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
