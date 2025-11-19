<?php

namespace Database\Seeders;

use App\Models\SettingOption;
use Illuminate\Database\Seeder;

class TaskPrioritiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * ClickUp-style task priorities
     */
    public function run(): void
    {
        $priorities = [
            [
                'label' => 'Urgent',
                'value' => 'urgent',
                'color_class' => '#ef4444', // red
                'sort_order' => 1,
            ],
            [
                'label' => 'High',
                'value' => 'high',
                'color_class' => '#f59e0b', // orange/amber
                'sort_order' => 2,
            ],
            [
                'label' => 'Normal',
                'value' => 'normal',
                'color_class' => '#3b82f6', // blue
                'sort_order' => 3,
            ],
            [
                'label' => 'Low',
                'value' => 'low',
                'color_class' => '#94a3b8', // slate/gray
                'sort_order' => 4,
            ],
        ];

        foreach ($priorities as $priority) {
            SettingOption::updateOrCreate(
                [
                    'category' => 'task_priorities',
                    'value' => $priority['value'],
                ],
                [
                    'label' => $priority['label'],
                    'color_class' => $priority['color_class'],
                    'sort_order' => $priority['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
