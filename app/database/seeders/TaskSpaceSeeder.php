<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TaskSpace;
use App\Models\User;

class TaskSpaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user for organization_id and user_id
        $user = User::first();

        if (!$user) {
            $this->command->warn('No users found. Please create a user first.');
            return;
        }

        // Create default spaces
        $spaces = [
            [
                'name' => 'Simplead',
                'icon' => '🚀',
                'color' => '#3b82f6',
                'position' => 0,
            ],
            [
                'name' => 'FEAA Galati',
                'icon' => '🎓',
                'color' => '#8b5cf6',
                'position' => 1,
            ],
        ];

        foreach ($spaces as $spaceData) {
            TaskSpace::firstOrCreate(
                [
                    'name' => $spaceData['name'],
                    'organization_id' => $user->organization_id,
                    'user_id' => $user->id,
                ],
                $spaceData
            );
        }

        $this->command->info('Default task spaces created successfully.');
    }
}
