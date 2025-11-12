<?php

namespace Database\Seeders;

use App\Models\SettingOption;
use Illuminate\Database\Seeder;

class AccessPlatformsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = [
            [
                'label' => 'cPanel',
                'value' => 'cpanel',
                'sort_order' => 1,
            ],
            [
                'label' => 'Hotjar',
                'value' => 'hotjar',
                'sort_order' => 2,
            ],
            [
                'label' => 'WP Admin',
                'value' => 'wp-admin',
                'sort_order' => 3,
            ],
            [
                'label' => 'Zapier',
                'value' => 'zapier',
                'sort_order' => 4,
            ],
            [
                'label' => 'Mailerlite',
                'value' => 'mailerlite',
                'sort_order' => 5,
            ],
            [
                'label' => 'Email',
                'value' => 'email',
                'sort_order' => 6,
            ],
            [
                'label' => 'VirtualMin',
                'value' => 'virtualmin',
                'sort_order' => 7,
            ],
            [
                'label' => 'DB',
                'value' => 'db',
                'sort_order' => 8,
            ],
        ];

        foreach ($platforms as $platform) {
            SettingOption::updateOrCreate(
                [
                    'category' => 'access_platforms',
                    'value' => $platform['value'],
                ],
                [
                    'label' => $platform['label'],
                    'sort_order' => $platform['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
