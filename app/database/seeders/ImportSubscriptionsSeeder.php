<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

class ImportSubscriptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user (you can change this to a specific user if needed)
        $user = User::first();

        if (!$user) {
            $this->command->error('No user found. Please create a user first.');
            return;
        }

        $subscriptions = [
            [
                'vendor_name' => 'Postmark',
                'price' => 78.00,
                'billing_cycle' => 'lunar',
                'next_renewal_date' => '2025-10-04',
                'status' => 'active',
            ],
            [
                'vendor_name' => 'Chat GPT',
                'price' => 88.00,
                'billing_cycle' => 'lunar',
                'next_renewal_date' => '2025-10-22',
                'status' => 'active',
            ],
            [
                'vendor_name' => 'Claude',
                'price' => 111.62,
                'billing_cycle' => 'lunar',
                'next_renewal_date' => '2025-11-20',
                'status' => 'active',
                'notes' => 'Plată în 7 zile',
            ],
            [
                'vendor_name' => 'Elementor',
                'price' => 971.00,
                'billing_cycle' => 'anual',
                'next_renewal_date' => '2025-11-25',
                'status' => 'active',
                'notes' => 'Plată în 12 zile',
            ],
            [
                'vendor_name' => 'Sintra',
                'price' => 998.00,
                'billing_cycle' => 'anual',
                'next_renewal_date' => '2026-01-07',
                'status' => 'active',
                'notes' => 'În 55 zile',
            ],
            [
                'vendor_name' => 'Smartbill',
                'price' => 131.00,
                'billing_cycle' => 'anual',
                'next_renewal_date' => '2026-02-15',
                'status' => 'active',
                'notes' => 'În 94 zile',
            ],
            [
                'vendor_name' => 'Adobe',
                'price' => 1797.19,
                'billing_cycle' => 'anual',
                'next_renewal_date' => '2026-02-17',
                'status' => 'active',
                'notes' => 'În 96 zile',
            ],
            [
                'vendor_name' => 'Gazduire VPS',
                'price' => 5248.00,
                'billing_cycle' => 'anual',
                'next_renewal_date' => '2026-05-02',
                'status' => 'active',
                'notes' => 'În 170 zile',
            ],
            [
                'vendor_name' => 'Gazduire email',
                'price' => 1088.00,
                'billing_cycle' => 'anual',
                'next_renewal_date' => '2026-06-30',
                'status' => 'active',
                'notes' => 'În 229 zile',
            ],
            [
                'vendor_name' => 'Rank Math',
                'price' => 1087.00,
                'billing_cycle' => 'anual',
                'next_renewal_date' => '2026-07-04',
                'status' => 'active',
                'notes' => 'În 233 zile',
            ],
            [
                'vendor_name' => 'Dropbox',
                'price' => 2184.00,
                'billing_cycle' => 'anual',
                'next_renewal_date' => '2026-07-24',
                'status' => 'active',
                'notes' => 'În 253 zile',
            ],
            [
                'vendor_name' => 'Gazduire server dev',
                'price' => 1087.00,
                'billing_cycle' => 'anual',
                'next_renewal_date' => '2026-07-31',
                'status' => 'active',
                'notes' => 'În 260 zile',
            ],
            [
                'vendor_name' => 'Complianz',
                'price' => 1400.00,
                'billing_cycle' => 'anual',
                'next_renewal_date' => '2026-09-22',
                'status' => 'paused',
            ],
        ];

        foreach ($subscriptions as $subscriptionData) {
            // Calculate start_date as one billing cycle before next_renewal_date
            $nextRenewal = Carbon::parse($subscriptionData['next_renewal_date']);

            if ($subscriptionData['billing_cycle'] === 'lunar') {
                $startDate = $nextRenewal->copy()->subMonth();
            } elseif ($subscriptionData['billing_cycle'] === 'anual') {
                $startDate = $nextRenewal->copy()->subYear();
            } else {
                $startDate = $nextRenewal->copy()->subDays($subscriptionData['custom_days'] ?? 30);
            }

            Subscription::withoutGlobalScope('user')->create([
                'user_id' => $user->id,
                'vendor_name' => $subscriptionData['vendor_name'],
                'price' => $subscriptionData['price'],
                'billing_cycle' => $subscriptionData['billing_cycle'],
                'custom_days' => $subscriptionData['custom_days'] ?? null,
                'start_date' => $startDate,
                'next_renewal_date' => $subscriptionData['next_renewal_date'],
                'status' => $subscriptionData['status'],
                'notes' => $subscriptionData['notes'] ?? null,
            ]);

            $this->command->info("Created subscription: {$subscriptionData['vendor_name']}");
        }

        $this->command->info('All subscriptions imported successfully!');
    }
}
