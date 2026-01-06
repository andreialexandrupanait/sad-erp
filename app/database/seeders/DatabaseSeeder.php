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
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * DatabaseSeeder - Development and Demo Data Only
 *
 * WARNING: This seeder contains sample data for development/demo purposes.
 * DO NOT run this seeder in production environments!
 *
 * The seeder will automatically abort if APP_ENV is set to 'production'.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // CRITICAL: Prevent seeding in production environment
        if (app()->environment('production')) {
            $this->command->error('⛔ ABORT: Cannot seed database in production environment!');
            $this->command->error('   This seeder is only for development and demo purposes.');
            return;
        }

        $this->command->warn('⚠️  Running development seeder - NOT for production use!');
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

        // Create Sample Credentials (with randomly generated demo passwords)
        $credentials = [
            [
                'client_id' => $clientRecords[0]->id,
                'platform' => 'Facebook',
                'url' => 'https://business.facebook.com',
                'username' => 'john@techsolutions.com',
                'password' => $this->generateDemoPassword(),
                'notes' => 'Facebook Business Manager account for advertising campaigns',
            ],
            [
                'client_id' => $clientRecords[0]->id,
                'platform' => 'Google Ads',
                'url' => 'https://ads.google.com',
                'username' => 'john@techsolutions.com',
                'password' => $this->generateDemoPassword(),
                'notes' => 'Google Ads account - Monthly budget $5000',
            ],
            [
                'client_id' => $clientRecords[1]->id,
                'platform' => 'WordPress',
                'url' => 'https://digitalmarketingpro.com/wp-admin',
                'username' => 'admin',
                'password' => $this->generateDemoPassword(),
                'notes' => 'Main website admin access',
            ],
            [
                'client_id' => $clientRecords[1]->id,
                'platform' => 'LinkedIn',
                'url' => 'https://linkedin.com',
                'username' => 'maria@digitalmarketingpro.com',
                'password' => $this->generateDemoPassword(),
                'notes' => 'Company LinkedIn page administrator',
            ],
            [
                'client_id' => $clientRecords[2]->id,
                'platform' => 'AWS',
                'url' => 'https://console.aws.amazon.com',
                'username' => 'robert@globalenterprises.com',
                'password' => $this->generateDemoPassword(),
                'notes' => 'AWS Console - Production environment access',
            ],
        ];

        foreach ($credentials as $credentialData) {
            Credential::create(array_merge($credentialData, [
                'organization_id' => $organization->id,
            ]));
        }

        // Create Sample Internal Accounts (with randomly generated demo passwords)
        $internalAccounts = [
            [
                'user_id' => $adminUser->id,
                'account_name' => 'Company Bank Account - Main',
                'url' => 'https://online-banking.example.com',
                'username' => 'democompany',
                'password' => $this->generateDemoPassword(),
                'team_accessible' => true,
                'notes' => 'Primary business checking account - Account #1234567890',
            ],
            [
                'user_id' => $adminUser->id,
                'account_name' => 'AWS Root Account',
                'url' => 'https://console.aws.amazon.com',
                'username' => 'root@democompany.com',
                'password' => $this->generateDemoPassword(),
                'team_accessible' => false,
                'notes' => 'AWS root account - DO NOT USE FOR DAILY OPERATIONS. Use IAM users instead.',
            ],
            [
                'user_id' => $adminUser->id,
                'account_name' => 'Stripe Payment Gateway',
                'url' => 'https://dashboard.stripe.com',
                'username' => 'billing@democompany.com',
                'password' => $this->generateDemoPassword(),
                'team_accessible' => true,
                'notes' => 'Live API keys stored separately. Test mode: enabled',
            ],
            [
                'user_id' => $managerUser->id,
                'account_name' => 'Mailchimp Marketing',
                'url' => 'https://mailchimp.com',
                'username' => 'marketing@democompany.com',
                'password' => $this->generateDemoPassword(),
                'team_accessible' => true,
                'notes' => 'Email marketing campaigns - Subscriber count: 15,000',
            ],
            [
                'user_id' => $managerUser->id,
                'account_name' => 'Domain Registrar - GoDaddy',
                'url' => 'https://sso.godaddy.com',
                'username' => 'admin@democompany.com',
                'password' => $this->generateDemoPassword(),
                'team_accessible' => false,
                'notes' => 'Domains: democompany.com, democompany.net (expires 2026-12)',
            ],
            [
                'user_id' => $adminUser->id,
                'account_name' => 'QuickBooks Accounting',
                'url' => 'https://quickbooks.intuit.com',
                'username' => 'accounting@democompany.com',
                'password' => $this->generateDemoPassword(),
                'team_accessible' => true,
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

        $this->command->info("\n✓ Database seeded successfully!");
        $this->command->info("✓ Created 1 organization: Demo Company Inc");
        $this->command->info("✓ Created 2 users");
        $this->command->info("✓ Created 3 sample clients");
        $this->command->info("✓ Created 5 sample credentials (with random passwords)");
        $this->command->info("✓ Created 6 sample internal accounts (with random passwords)");
        $this->command->info("✓ Created 7 sample domains (1 expired, 2 expiring soon, 4 valid)");
        $this->command->info("✓ Created 10 sample subscriptions (1 overdue, 3 urgent/warning, 5 active, 1 paused, 1 cancelled)\n");
        $this->command->warn("Login credentials:");
        $this->command->line("  Admin:   admin@example.com / password");
        $this->command->line("  Manager: manager@example.com / password\n");
    }

    /**
     * Generate a random secure password for demo purposes.
     *
     * @return string Random 16-character password
     */
    private function generateDemoPassword(): string
    {
        return Str::password(16);
    }
}
