<?php

namespace Database\Seeders;

use App\Models\ClientSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user (or you can customize this)
        $user = User::first();

        if (!$user) {
            $this->command->warn('No users found. Please create a user first.');
            return;
        }

        $defaultStatuses = [
            [
                'user_id' => $user->id,
                'name' => 'Prospect',
                'color' => '#6B7280',
                'color_background' => '#F3F4F6',
                'color_text' => '#374151',
                'order_index' => 1,
                'is_active' => true,
            ],
            [
                'user_id' => $user->id,
                'name' => 'Active',
                'color' => '#10B981',
                'color_background' => '#D1FAE5',
                'color_text' => '#065F46',
                'order_index' => 2,
                'is_active' => true,
            ],
            [
                'user_id' => $user->id,
                'name' => 'In Progress',
                'color' => '#3B82F6',
                'color_background' => '#DBEAFE',
                'color_text' => '#1E40AF',
                'order_index' => 3,
                'is_active' => true,
            ],
            [
                'user_id' => $user->id,
                'name' => 'On Hold',
                'color' => '#F59E0B',
                'color_background' => '#FEF3C7',
                'color_text' => '#92400E',
                'order_index' => 4,
                'is_active' => true,
            ],
            [
                'user_id' => $user->id,
                'name' => 'Completed',
                'color' => '#8B5CF6',
                'color_background' => '#EDE9FE',
                'color_text' => '#5B21B6',
                'order_index' => 5,
                'is_active' => true,
            ],
            [
                'user_id' => $user->id,
                'name' => 'Inactive',
                'color' => '#EF4444',
                'color_background' => '#FEE2E2',
                'color_text' => '#991B1B',
                'order_index' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($defaultStatuses as $status) {
            ClientSetting::create($status);
        }

        $this->command->info('Default client statuses created successfully!');
    }
}
