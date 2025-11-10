<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User ID - will be set to auth user when running, or use first user
        $userId = auth()->id() ?? 1;

        $subscriptions = [
            [
                'vendor_name' => 'Postmark',
                'price' => 78.00,
                'billing_cycle' => 'monthly',
                'status' => 'active',
                'next_renewal_date' => '2025-10-04',
                'start_date' => '2024-10-04',
            ],
            [
                'vendor_name' => 'Chat GPT',
                'price' => 88.00,
                'billing_cycle' => 'monthly',
                'status' => 'active',
                'next_renewal_date' => '2025-10-22',
                'start_date' => '2024-10-22',
            ],
            [
                'vendor_name' => 'Claude',
                'price' => 111.62,
                'billing_cycle' => 'monthly',
                'status' => 'active',
                'next_renewal_date' => '2025-11-20',
                'start_date' => '2024-11-20',
                'notes' => 'Plată în 10 zile',
            ],
            [
                'vendor_name' => 'Elementor',
                'price' => 971.00,
                'billing_cycle' => 'annual',
                'status' => 'active',
                'next_renewal_date' => '2025-11-25',
                'start_date' => '2024-11-25',
                'notes' => 'În 15 zile',
            ],
            [
                'vendor_name' => 'Sintra',
                'price' => 998.00,
                'billing_cycle' => 'annual',
                'status' => 'active',
                'next_renewal_date' => '2026-01-07',
                'start_date' => '2025-01-07',
                'notes' => 'În 58 zile',
            ],
            [
                'vendor_name' => 'Smartbill',
                'price' => 131.00,
                'billing_cycle' => 'annual',
                'status' => 'active',
                'next_renewal_date' => '2026-02-15',
                'start_date' => '2025-02-15',
                'notes' => 'În 97 zile',
            ],
            [
                'vendor_name' => 'Adobe',
                'price' => 1797.19,
                'billing_cycle' => 'annual',
                'status' => 'active',
                'next_renewal_date' => '2026-02-17',
                'start_date' => '2025-02-17',
                'notes' => 'În 99 zile',
            ],
            [
                'vendor_name' => 'Gazduire VPS',
                'price' => 5248.00,
                'billing_cycle' => 'annual',
                'status' => 'active',
                'next_renewal_date' => '2026-05-02',
                'start_date' => '2025-05-02',
                'notes' => 'În 173 zile',
            ],
            [
                'vendor_name' => 'Gazduire email',
                'price' => 1088.00,
                'billing_cycle' => 'annual',
                'status' => 'active',
                'next_renewal_date' => '2026-06-30',
                'start_date' => '2025-06-30',
                'notes' => 'În 232 zile',
            ],
            [
                'vendor_name' => 'Rank Math',
                'price' => 1087.00,
                'billing_cycle' => 'annual',
                'status' => 'active',
                'next_renewal_date' => '2026-07-04',
                'start_date' => '2025-07-04',
                'notes' => 'În 236 zile',
            ],
            [
                'vendor_name' => 'Dropbox',
                'price' => 2184.00,
                'billing_cycle' => 'annual',
                'status' => 'active',
                'next_renewal_date' => '2026-07-24',
                'start_date' => '2025-07-24',
                'notes' => 'În 256 zile',
            ],
            [
                'vendor_name' => 'Gazduire server dev',
                'price' => 1087.00,
                'billing_cycle' => 'annual',
                'status' => 'active',
                'next_renewal_date' => '2026-07-31',
                'start_date' => '2025-07-31',
                'notes' => 'În 263 zile',
            ],
            [
                'vendor_name' => 'Complianz',
                'price' => 1400.00,
                'billing_cycle' => 'annual',
                'status' => 'paused',
                'next_renewal_date' => '2026-09-22',
                'start_date' => '2025-09-22',
            ],
        ];

        foreach ($subscriptions as $subscriptionData) {
            // Parse dates
            $nextRenewalDate = Carbon::parse($subscriptionData['next_renewal_date']);
            $startDate = Carbon::parse($subscriptionData['start_date']);

            Subscription::create([
                'user_id' => $userId,
                'vendor_name' => $subscriptionData['vendor_name'],
                'price' => $subscriptionData['price'],
                'billing_cycle' => $subscriptionData['billing_cycle'],
                'status' => $subscriptionData['status'],
                'next_renewal_date' => $nextRenewalDate,
                'start_date' => $startDate,
                'notes' => $subscriptionData['notes'] ?? null,
            ]);
        }

        $this->command->info('Successfully imported ' . count($subscriptions) . ' subscriptions!');
    }
}
