<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Domain;
use Illuminate\Support\Facades\DB;

class DomainDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get active status ID from client_settings table
        $activeStatusId = DB::table('client_settings')->where('name', 'Active')->first()->id;

        // Get the authenticated user ID (or use a default)
        $userId = 1; // Default to first user

        // First, create clients that don't exist yet
        $clients = [
            'Paul Ardeleanu' => Client::firstOrCreate(
                ['name' => 'Paul Ardeleanu'],
                [
                    'company_name' => 'Paul Ardeleanu',
                    'email' => 'contact@paulardeleanu.ro',
                    'phone' => '',
                    'status_id' => $activeStatusId,
                    'user_id' => $userId,
                ]
            ),
            'Florin Pasat' => Client::firstOrCreate(
                ['name' => 'Florin Pasat'],
                [
                    'company_name' => 'Florin Pasat',
                    'email' => 'contact@florinpasat.com',
                    'phone' => '',
                    'status_id' => $activeStatusId,
                    'user_id' => $userId,
                ]
            ),
            'Priority Clinic' => Client::firstOrCreate(
                ['name' => 'Priority Clinic'],
                [
                    'company_name' => 'Priority Clinic',
                    'email' => 'contact@priority-clinic.ro',
                    'phone' => '',
                    'status_id' => $activeStatusId,
                    'user_id' => $userId,
                ]
            ),
            'International School of Oradea' => Client::firstOrCreate(
                ['name' => 'International School of Oradea'],
                [
                    'company_name' => 'International School of Oradea',
                    'email' => 'contact@isor.ro',
                    'phone' => '',
                    'status_id' => $activeStatusId,
                    'user_id' => $userId,
                ]
            ),
            'Speaker Marketing' => Client::firstOrCreate(
                ['name' => 'Speaker Marketing'],
                [
                    'company_name' => 'Speaker Marketing',
                    'email' => 'contact@marketingdeck.com',
                    'phone' => '',
                    'status_id' => $activeStatusId,
                    'user_id' => $userId,
                ]
            ),
            'Fastrackids' => Client::firstOrCreate(
                ['name' => 'Fastrackids'],
                [
                    'company_name' => 'Fastrackids',
                    'email' => 'contact@fastrackids.ro',
                    'phone' => '',
                    'status_id' => $activeStatusId,
                    'user_id' => $userId,
                ]
            ),
        ];

        // Now create the domains with their data from the screenshot
        $domains = [
            [
                'domain_name' => 'feaagalati.ro',
                'client_id' => null, // No client specified in screenshot
                'registrar' => 'rotld',
                'status' => 'active',
                'registration_date' => '2023-05-15',
                'expiry_date' => '2028-05-13',
                'annual_cost' => null,
                'auto_renew' => false,
            ],
            [
                'domain_name' => 'paulardeleanu.ro',
                'client_id' => $clients['Paul Ardeleanu']->id,
                'registrar' => 'hostvision',
                'status' => 'active',
                'registration_date' => '2012-01-10',
                'expiry_date' => '2028-09-17',
                'annual_cost' => null,
                'auto_renew' => false,
            ],
            [
                'domain_name' => 'cursuridonbosco.ro',
                'client_id' => null, // No client specified in screenshot
                'registrar' => 'rotld',
                'status' => 'active',
                'registration_date' => '2018-03-22',
                'expiry_date' => '2027-03-20',
                'annual_cost' => null,
                'auto_renew' => false,
            ],
            [
                'domain_name' => 'florinpasat.com',
                'client_id' => $clients['Florin Pasat']->id,
                'registrar' => 'tucows',
                'status' => 'active',
                'registration_date' => '2014-01-14',
                'expiry_date' => '2026-01-14',
                'annual_cost' => null,
                'auto_renew' => false,
            ],
            [
                'domain_name' => 'priority-clinic.ro',
                'client_id' => $clients['Priority Clinic']->id,
                'registrar' => 'gazduire-net',
                'status' => 'active',
                'registration_date' => '2018-10-11',
                'expiry_date' => '2027-10-19',
                'annual_cost' => null,
                'auto_renew' => false,
            ],
            [
                'domain_name' => 'isor.ro',
                'client_id' => $clients['International School of Oradea']->id,
                'registrar' => 'rotld',
                'status' => 'active',
                'registration_date' => '2017-04-18',
                'expiry_date' => '2032-04-14',
                'annual_cost' => null,
                'auto_renew' => false,
            ],
            [
                'domain_name' => 'marketingdeck.com',
                'client_id' => $clients['Speaker Marketing']->id,
                'registrar' => 'name-bright',
                'status' => 'active',
                'registration_date' => '2020-03-12',
                'expiry_date' => '2026-08-23',
                'annual_cost' => null,
                'auto_renew' => false,
            ],
            [
                'domain_name' => 'beekids.ro',
                'client_id' => $clients['Fastrackids']->id,
                'registrar' => 'romarg',
                'status' => 'active',
                'registration_date' => '2021-09-09',
                'expiry_date' => '2026-09-09',
                'annual_cost' => null,
                'auto_renew' => false,
            ],
            [
                'domain_name' => 'fastrackids.ro',
                'client_id' => $clients['Fastrackids']->id,
                'registrar' => 'romarg',
                'status' => 'active',
                'registration_date' => '2006-07-26',
                'expiry_date' => '2033-04-11',
                'annual_cost' => null,
                'auto_renew' => false,
            ],
        ];

        // Insert domains
        foreach ($domains as $domainData) {
            // Set organization_id to 1 (default organization)
            $domainData['organization_id'] = 1;

            Domain::withoutGlobalScope('organization')->updateOrCreate(
                ['domain_name' => $domainData['domain_name']],
                $domainData
            );
        }

        $this->command->info('Successfully imported ' . count($domains) . ' domains!');
    }
}
