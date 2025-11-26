<?php

namespace Database\Seeders;

use App\Models\SettingOption;
use Illuminate\Database\Seeder;

class TaskStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * ClickUp-style task statuses with semantic meaning
     */
    public function run(): void
    {
        $statuses = [
            [
                'label' => 'To Do',
                'value' => 'todo',
                'color_class' => '#94a3b8', // slate
                'sort_order' => 1,
            ],
            [
                'label' => 'In Progress',
                'value' => 'in-progress',
                'color_class' => '#3b82f6', // blue
                'sort_order' => 2,
            ],
            [
                'label' => 'In Review',
                'value' => 'in-review',
                'color_class' => '#a855f7', // purple
                'sort_order' => 3,
            ],
            [
                'label' => 'Blocked',
                'value' => 'blocked',
                'color_class' => '#ef4444', // red
                'sort_order' => 4,
            ],
            [
                'label' => 'Completed',
                'value' => 'completed',
                'color_class' => '#22c55e', // green
                'sort_order' => 5,
            ],
        ];

        foreach ($statuses as $status) {
            SettingOption::updateOrCreate(
                [
                    'category' => 'task_statuses',
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
