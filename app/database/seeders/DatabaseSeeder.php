<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use App\Models\Client;
use App\Models\Credential;
use App\Models\InternalAccount;
use App\Models\Domain;
use App\Models\Subscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Demo Organization
        $organization = Organization::create([
            'name' => 'Demo Company Inc',
            'slug' => 'demo-company',
            'email' => 'info@democompany.com',
            'phone' => '+1-555-123-4567',
            'address' => '123 Business Street',
            'tax_id' => 'TAX-123456',
            'billing_email' => 'billing@democompany.com',
            'status' => 'active',
        ]);

        // Create Admin User
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'organization_id' => $organization->id,
            'role' => 'admin',
            'phone' => '+1-555-999-0001',
            'status' => 'active',
        ]);

        // Create Manager User
        $managerUser = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'organization_id' => $organization->id,
            'role' => 'manager',
            'phone' => '+1-555-999-0002',
            'status' => 'active',
        ]);

        // Create Sample Clients
        $clients = [
            [
                'name' => 'John Smith',
                'company_name' => 'Tech Solutions Ltd',
                'email' => 'john@techsolutions.com',
                'phone' => '+1-555-100-0001',
                'address' => 'San Francisco, California, United States',
            ],
            [
                'name' => 'Maria Garcia',
                'company_name' => 'Digital Marketing Pro',
                'email' => 'maria@digitalmarketingpro.com',
                'phone' => '+1-555-200-0002',
                'address' => 'New York, United States',
            ],
            [
                'name' => 'Robert Johnson',
                'company_name' => 'Global Enterprises Inc',
                'email' => 'robert@globalenterprises.com',
                'phone' => '+1-555-300-0003',
                'address' => 'Chicago, United States',
            ],
        ];

        $clientRecords = [];
        foreach ($clients as $clientData) {
            $clientRecords[] = Client::create(array_merge($clientData, [
                'user_id' => $adminUser->id,
            ]));
        }

        // Create Sample Credentials
        $credentials = [
            [
                'client_id' => $clientRecords[0]->id,
                'platform' => 'Facebook',
                'url' => 'https://business.facebook.com',
                'username' => 'john@techsolutions.com',
                'password' => 'SecurePass123!',
                'notes' => 'Facebook Business Manager account for advertising campaigns',
            ],
            [
                'client_id' => $clientRecords[0]->id,
                'platform' => 'Google Ads',
                'url' => 'https://ads.google.com',
                'username' => 'john@techsolutions.com',
                'password' => 'GoogleAds2025',
                'notes' => 'Google Ads account - Monthly budget $5000',
            ],
            [
                'client_id' => $clientRecords[1]->id,
                'platform' => 'WordPress',
                'url' => 'https://digitalmarketingpro.com/wp-admin',
                'username' => 'admin',
                'password' => 'WP@dmin2025',
                'notes' => 'Main website admin access',
            ],
            [
                'client_id' => $clientRecords[1]->id,
                'platform' => 'LinkedIn',
                'url' => 'https://linkedin.com',
                'username' => 'maria@digitalmarketingpro.com',
                'password' => 'LinkedIn#2025',
                'notes' => 'Company LinkedIn page administrator',
            ],
            [
                'client_id' => $clientRecords[2]->id,
                'platform' => 'AWS',
                'url' => 'https://console.aws.amazon.com',
                'username' => 'robert@globalenterprises.com',
                'password' => 'AWS_Secure_Pass_2025',
                'notes' => 'AWS Console - Production environment access',
            ],
        ];

        foreach ($credentials as $credentialData) {
            Credential::create(array_merge($credentialData, [
                'organization_id' => $organization->id,
            ]));
        }

        // Create Sample Internal Accounts
        $internalAccounts = [
            [
                'user_id' => $adminUser->id,
                'nume_cont_aplicatie' => 'Company Bank Account - Main',
                'platforma' => 'Bank Account',
                'url' => 'https://online-banking.example.com',
                'username' => 'democompany',
                'password' => 'BankSecure2025!',
                'accesibil_echipei' => true,
                'notes' => 'Primary business checking account - Account #1234567890',
            ],
            [
                'user_id' => $adminUser->id,
                'nume_cont_aplicatie' => 'AWS Root Account',
                'platforma' => 'Cloud Storage',
                'url' => 'https://console.aws.amazon.com',
                'username' => 'root@democompany.com',
                'password' => 'AWS_R00t_P@ss_2025',
                'accesibil_echipei' => false,
                'notes' => 'AWS root account - DO NOT USE FOR DAILY OPERATIONS. Use IAM users instead.',
            ],
            [
                'user_id' => $adminUser->id,
                'nume_cont_aplicatie' => 'Stripe Payment Gateway',
                'platforma' => 'Payment Gateway',
                'url' => 'https://dashboard.stripe.com',
                'username' => 'billing@democompany.com',
                'password' => 'Stripe_Secure_2025',
                'accesibil_echipei' => true,
                'notes' => 'Live API keys stored separately. Test mode: enabled',
            ],
            [
                'user_id' => $managerUser->id,
                'nume_cont_aplicatie' => 'Mailchimp Marketing',
                'platforma' => 'Email Service',
                'url' => 'https://mailchimp.com',
                'username' => 'marketing@democompany.com',
                'password' => 'Mail_Ch1mp_2025',
                'accesibil_echipei' => true,
                'notes' => 'Email marketing campaigns - Subscriber count: 15,000',
            ],
            [
                'user_id' => $managerUser->id,
                'nume_cont_aplicatie' => 'Domain Registrar - GoDaddy',
                'platforma' => 'Domain Registrar',
                'url' => 'https://sso.godaddy.com',
                'username' => 'admin@democompany.com',
                'password' => 'G0Daddy_Secure_2025!',
                'accesibil_echipei' => false,
                'notes' => 'Domains: democompany.com, democompany.net (expires 2026-12)',
            ],
            [
                'user_id' => $adminUser->id,
                'nume_cont_aplicatie' => 'QuickBooks Accounting',
                'platforma' => 'Accounting Software',
                'url' => 'https://quickbooks.intuit.com',
                'username' => 'accounting@democompany.com',
                'password' => 'QB_Acc0unting_2025',
                'accesibil_echipei' => true,
                'notes' => 'Fiscal year: Jan-Dec. Accountant access: enabled',
            ],
        ];

        foreach ($internalAccounts as $accountData) {
            InternalAccount::create(array_merge($accountData, [
                'organization_id' => $organization->id,
            ]));
        }

        // Create Sample Domains with varying expiry dates
        $domains = [
            [
                'client_id' => $clientRecords[0]->id,
                'domain_name' => 'techsolutions.com',
                'registrar' => 'GoDaddy',
                'status' => 'Active',
                'registration_date' => Carbon::now()->subYears(3),
                'expiry_date' => Carbon::now()->addDays(45), // Valid (>30 days)
                'annual_cost' => 12.99,
                'auto_renew' => true,
                'notes' => 'Primary business domain',
            ],
            [
                'client_id' => $clientRecords[0]->id,
                'domain_name' => 'techsolutions.net',
                'registrar' => 'Namecheap',
                'status' => 'Active',
                'registration_date' => Carbon::now()->subYears(2),
                'expiry_date' => Carbon::now()->addDays(15), // Expiring soon (< 30 days)
                'annual_cost' => 10.99,
                'auto_renew' => false,
                'notes' => 'Backup domain - needs renewal',
            ],
            [
                'client_id' => $clientRecords[1]->id,
                'domain_name' => 'digitalmarketingpro.com',
                'registrar' => 'Google Domains',
                'status' => 'Active',
                'registration_date' => Carbon::now()->subYears(4),
                'expiry_date' => Carbon::now()->addMonths(6), // Valid
                'annual_cost' => 15.00,
                'auto_renew' => true,
                'notes' => 'Main business website',
            ],
            [
                'client_id' => $clientRecords[1]->id,
                'domain_name' => 'marketingpro.io',
                'registrar' => 'Namecheap',
                'status' => 'Expired',
                'registration_date' => Carbon::now()->subYears(2),
                'expiry_date' => Carbon::now()->subDays(10), // Expired
                'annual_cost' => 25.00,
                'auto_renew' => false,
                'notes' => 'URGENT: Expired 10 days ago!',
            ],
            [
                'client_id' => $clientRecords[2]->id,
                'domain_name' => 'globalenterprises.com',
                'registrar' => 'Cloudflare',
                'status' => 'Active',
                'registration_date' => Carbon::now()->subYears(5),
                'expiry_date' => Carbon::now()->addYear(), // Valid
                'annual_cost' => 9.99,
                'auto_renew' => true,
                'notes' => 'Corporate domain with Cloudflare DNS',
            ],
            [
                'client_id' => null, // No client assigned
                'domain_name' => 'democompany.com',
                'registrar' => 'GoDaddy',
                'status' => 'Active',
                'registration_date' => Carbon::now()->subYears(6),
                'expiry_date' => Carbon::now()->addDays(5), // Expiring very soon
                'annual_cost' => 12.99,
                'auto_renew' => false,
                'notes' => 'Company main domain - RENEW ASAP!',
            ],
            [
                'client_id' => null,
                'domain_name' => 'democompany.net',
                'registrar' => 'GoDaddy',
                'status' => 'Active',
                'registration_date' => Carbon::now()->subYears(5),
                'expiry_date' => Carbon::now()->addMonths(3), // Valid
                'annual_cost' => 12.99,
                'auto_renew' => true,
            ],
        ];

        foreach ($domains as $domainData) {
            Domain::create(array_merge($domainData, [
                'organization_id' => $organization->id,
            ]));
        }

        // Create Sample Subscriptions with varying renewal dates
        $subscriptions = [
            [
                'user_id' => $adminUser->id,
                'vendor_name' => 'Adobe Creative Cloud',
                'price' => 249.99,
                'billing_cycle' => 'monthly',
                'custom_days' => null,
                'start_date' => Carbon::now()->subMonths(6),
                'next_renewal_date' => Carbon::now()->addDays(3), // Urgent (0-7 days)
                'status' => 'active',
                'notes' => 'Team subscription - 5 licenses for design team',
            ],
            [
                'user_id' => $adminUser->id,
                'vendor_name' => 'Microsoft 365 Business',
                'price' => 599.00,
                'billing_cycle' => 'monthly',
                'custom_days' => null,
                'start_date' => Carbon::now()->subYear(),
                'next_renewal_date' => Carbon::now()->addDays(12), // Warning (8-14 days)
                'status' => 'active',
                'notes' => 'Company-wide Office suite and email',
            ],
            [
                'user_id' => $adminUser->id,
                'vendor_name' => 'AWS Cloud Services',
                'price' => 1250.00,
                'billing_cycle' => 'monthly',
                'custom_days' => null,
                'start_date' => Carbon::now()->subYears(2),
                'next_renewal_date' => Carbon::now()->addDays(20), // Normal (>14 days)
                'status' => 'active',
                'notes' => 'Production servers and storage - EC2, S3, RDS',
            ],
            [
                'user_id' => $managerUser->id,
                'vendor_name' => 'Slack Business+',
                'price' => 799.00,
                'billing_cycle' => 'annual',
                'custom_days' => null,
                'start_date' => Carbon::now()->subMonths(8),
                'next_renewal_date' => Carbon::now()->addMonths(4), // Normal
                'status' => 'active',
                'notes' => 'Team communication platform - 50 users',
            ],
            [
                'user_id' => $managerUser->id,
                'vendor_name' => 'Mailchimp Pro',
                'price' => 149.99,
                'billing_cycle' => 'monthly',
                'custom_days' => null,
                'start_date' => Carbon::now()->subMonths(3),
                'next_renewal_date' => Carbon::now()->subDays(5), // Overdue!
                'status' => 'active',
                'notes' => 'Email marketing - 15,000 subscribers',
            ],
            [
                'user_id' => $adminUser->id,
                'vendor_name' => 'GitHub Team',
                'price' => 89.99,
                'billing_cycle' => 'monthly',
                'custom_days' => null,
                'start_date' => Carbon::now()->subMonths(14),
                'next_renewal_date' => Carbon::now()->addDays(8), // Warning
                'status' => 'active',
                'notes' => 'Code repository - 10 private repos',
            ],
            [
                'user_id' => $managerUser->id,
                'vendor_name' => 'Zoom Business',
                'price' => 299.99,
                'billing_cycle' => 'annual',
                'custom_days' => null,
                'start_date' => Carbon::now()->subYear(),
                'next_renewal_date' => Carbon::now()->addMonths(2),
                'status' => 'paused',
                'notes' => 'Video conferencing - Paused due to remote work reduction',
            ],
            [
                'user_id' => $adminUser->id,
                'vendor_name' => 'Dropbox Business',
                'price' => 450.00,
                'billing_cycle' => 'annual',
                'custom_days' => null,
                'start_date' => Carbon::now()->subYears(2),
                'next_renewal_date' => Carbon::now()->subMonths(1),
                'status' => 'cancelled',
                'notes' => 'Cancelled - Migrated to Google Workspace',
            ],
            [
                'user_id' => $managerUser->id,
                'vendor_name' => 'Asana Premium',
                'price' => 39.99,
                'billing_cycle' => 'custom',
                'custom_days' => 90, // Quarterly
                'start_date' => Carbon::now()->subMonths(9),
                'next_renewal_date' => Carbon::now()->addDays(15), // Normal
                'status' => 'active',
                'notes' => 'Project management - Quarterly billing (90 days)',
            ],
            [
                'user_id' => $adminUser->id,
                'vendor_name' => 'CloudFlare Pro',
                'price' => 240.00,
                'billing_cycle' => 'annual',
                'custom_days' => null,
                'start_date' => Carbon::now()->subMonths(2),
                'next_renewal_date' => Carbon::now()->addMonths(10),
                'status' => 'active',
                'notes' => 'CDN and security - All company domains',
            ],
        ];

        foreach ($subscriptions as $subscriptionData) {
            Subscription::create(array_merge($subscriptionData, [
                'organization_id' => $organization->id,
            ]));
        }

        echo "\n✓ Database seeded successfully!\n";
        echo "✓ Created 1 organization: Demo Company Inc\n";
        echo "✓ Created 2 users\n";
        echo "✓ Created 3 sample clients\n";
        echo "✓ Created 5 sample credentials\n";
        echo "✓ Created 6 sample internal accounts\n";
        echo "✓ Created 7 sample domains (1 expired, 2 expiring soon, 4 valid)\n";
        echo "✓ Created 10 sample subscriptions (1 overdue, 3 urgent/warning, 5 active, 1 paused, 1 cancelled)\n\n";
        echo "Login credentials:\n";
        echo "  Admin:   admin@example.com / password\n";
        echo "  Manager: manager@example.com / password\n\n";
    }
}
