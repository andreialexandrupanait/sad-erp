<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\FinancialRevenue;
use App\Services\SmartbillService;
use Illuminate\Support\Facades\Auth;

class PopulateSmartbillClientsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smartbill:populate-clients
                            {--dry-run : Run without making changes}
                            {--client= : Specific client ID to populate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate placeholder clients with real data from Smartbill API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $specificClientId = $this->option('client');

        $this->info('🔍 Starting Smartbill client population...');
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get placeholder clients
        $query = Client::withoutGlobalScope('user_scope')
            ->where(function($q) {
                $q->where('name', 'like', 'Client CIF%')
                  ->orWhere('notes', 'like', '%Auto-created from Smartbill import%');
            });

        if ($specificClientId) {
            $query->where('id', $specificClientId);
        }

        $placeholderClients = $query->get();

        if ($placeholderClients->isEmpty()) {
            $this->info('✅ No placeholder clients found. All clients already have proper names!');
            return 0;
        }

        $this->info("Found {$placeholderClients->count()} placeholder client(s) to populate");
        $this->newLine();

        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($placeholderClients as $client) {
            $this->info("Processing: Client #{$client->id} - {$client->name} (CIF: {$client->tax_id})");

            try {
                // Find a revenue linked to this client that has Smartbill data
                $revenue = FinancialRevenue::withoutGlobalScope('user_scope')
                    ->where('client_id', $client->id)
                    ->whereNotNull('smartbill_series')
                    ->whereNotNull('smartbill_invoice_number')
                    ->first();

                if (!$revenue) {
                    $this->warn("  ⚠️  No revenues with Smartbill data found for this client");
                    $skipped++;
                    continue;
                }

                $this->line("  📄 Using invoice: {$revenue->smartbill_series}-{$revenue->smartbill_invoice_number}");

                // Get Smartbill credentials from organization settings
                $organization = $revenue->organization;
                $smartbillSettings = $organization->settings['smartbill'] ?? [];

                if (empty($smartbillSettings['username']) || empty($smartbillSettings['token']) || empty($smartbillSettings['cif'])) {
                    $this->error("  ❌ Smartbill credentials not configured for organization #{$organization->id}");
                    $errors++;
                    continue;
                }

                // Fetch invoice from Smartbill API
                $smartbillService = new SmartbillService(
                    $smartbillSettings['username'],
                    $smartbillSettings['token'],
                    $smartbillSettings['cif']
                );

                $this->line("  🌐 Fetching invoice from Smartbill API...");
                $invoiceData = $smartbillService->getInvoice(
                    $revenue->smartbill_series,
                    $revenue->smartbill_invoice_number
                );

                if (!$invoiceData || !isset($invoiceData['client'])) {
                    $this->error("  ❌ Failed to fetch invoice data from Smartbill API");
                    $errors++;
                    continue;
                }

                // Extract client data
                $clientData = $invoiceData['client'];
                $updates = [];

                // Update name
                if (!empty($clientData['name'])) {
                    $updates['name'] = $clientData['name'];
                    $updates['company_name'] = $clientData['name'];
                    $this->line("  ✏️  Name: {$clientData['name']}");
                }

                // Update email
                if (!empty($clientData['email'])) {
                    $updates['email'] = $clientData['email'];
                    $this->line("  ✉️  Email: {$clientData['email']}");
                }

                // Update phone
                if (!empty($clientData['phone'])) {
                    $updates['phone'] = $clientData['phone'];
                    $this->line("  📞 Phone: {$clientData['phone']}");
                }

                // Update address
                $addressParts = [];
                if (!empty($clientData['address'])) $addressParts[] = $clientData['address'];
                if (!empty($clientData['city'])) $addressParts[] = $clientData['city'];
                if (!empty($clientData['county'])) $addressParts[] = $clientData['county'];
                if (!empty($clientData['country'])) $addressParts[] = $clientData['country'];

                if (!empty($addressParts)) {
                    $updates['address'] = implode(', ', $addressParts);
                    $this->line("  📍 Address: {$updates['address']}");
                }

                // Update VAT payer status
                if (isset($clientData['isTaxPayer'])) {
                    $updates['vat_payer'] = (bool) $clientData['isTaxPayer'];
                    $this->line("  💰 VAT Payer: " . ($updates['vat_payer'] ? 'Yes' : 'No'));
                }

                // Update registration number
                if (!empty($clientData['regCom'])) {
                    $updates['registration_number'] = $clientData['regCom'];
                    $this->line("  🏢 Reg. Com.: {$clientData['regCom']}");
                }

                // Update notes
                $updates['notes'] = 'Enriched with data from Smartbill API on ' . now()->format('Y-m-d H:i');

                if (empty($updates)) {
                    $this->warn("  ⚠️  No data to update");
                    $skipped++;
                    continue;
                }

                // Apply updates
                if (!$isDryRun) {
                    $client->update($updates);
                    $this->info("  ✅ Client updated successfully");
                } else {
                    $this->warn("  🔍 DRY RUN - Would update client with above data");
                }

                $updated++;

            } catch (\Exception $e) {
                $this->error("  ❌ Error: {$e->getMessage()}");
                $errors++;
            }

            $this->newLine();
        }

        // Summary
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('Summary:');
        $this->info("  ✅ Updated: {$updated}");
        $this->info("  ⚠️  Skipped: {$skipped}");
        $this->info("  ❌ Errors: {$errors}");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return $errors > 0 ? 1 : 0;
    }
}
