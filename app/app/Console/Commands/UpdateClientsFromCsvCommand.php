<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UpdateClientsFromCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smartbill:update-clients-from-csv
                            {file : Path to the Smartbill CSV/XLS file}
                            {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update placeholder clients with real names from Smartbill CSV/XLS export';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $isDryRun = $this->option('dry-run');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info('📂 Reading file: ' . basename($filePath));
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        $this->newLine();

        // Detect file type and parse
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (in_array($extension, ['xls', 'xlsx'])) {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $csvData = $worksheet->toArray();
        } else {
            $csvData = array_map('str_getcsv', file($filePath));
        }

        // Find header row (Smartbill exports have metadata rows before headers)
        $headerRowIndex = 0;
        $header = null;

        foreach ($csvData as $index => $row) {
            $row = array_map('trim', $row);
            $hasSmartbillColumns = false;
            foreach ($row as $cell) {
                $cell = strtolower($cell);
                if (in_array($cell, ['client', 'cif', 'factura', 'data'])) {
                    $hasSmartbillColumns = true;
                    break;
                }
            }

            if ($hasSmartbillColumns) {
                $headerRowIndex = $index;
                $header = $row;
                break;
            }
        }

        if ($header === null) {
            $header = array_shift($csvData);
            $header = array_map('trim', $header);
        } else {
            $csvData = array_slice($csvData, $headerRowIndex + 1);
        }

        $this->info("Found " . count($csvData) . " data rows");
        $this->info("Columns: " . implode(', ', $header));
        $this->newLine();

        // Extract unique clients from CSV
        $clientsInCsv = [];
        foreach ($csvData as $row) {
            if (empty(array_filter($row))) continue;

            $data = array_combine($header, $row);
            $cif = trim($data['CIF'] ?? '');
            $clientName = trim($data['Client'] ?? '');

            if (empty($cif)) continue;

            $cleanCif = preg_replace('/^RO/i', '', $cif);
            $cleanCif = preg_replace('/\s+/', '', $cleanCif);

            if (!isset($clientsInCsv[$cleanCif]) && !empty($clientName)) {
                $clientsInCsv[$cleanCif] = [
                    'cif' => $cif,
                    'name' => $clientName,
                    'clean_cif' => $cleanCif
                ];
            }
        }

        $this->info("Found " . count($clientsInCsv) . " unique clients in CSV");
        $this->newLine();

        // Get placeholder clients
        $placeholderClients = Client::withoutGlobalScope('user_scope')
            ->where(function($q) {
                $q->where('name', 'like', 'Client CIF%')
                  ->orWhere('notes', 'like', '%Auto-created from Smartbill import%');
            })
            ->get();

        $this->info("Found {$placeholderClients->count()} placeholder clients in database");
        $this->newLine();

        $updated = 0;
        $notFound = 0;

        foreach ($placeholderClients as $client) {
            $this->line("Processing: Client #{$client->id} - {$client->name} (CIF: {$client->tax_id})");

            // Clean CIF for matching
            $dbCif = trim($client->tax_id);
            $cleanDbCif = preg_replace('/^RO/i', '', $dbCif);
            $cleanDbCif = preg_replace('/\s+/', '', $cleanDbCif);

            // Try to find in CSV
            $csvClient = $clientsInCsv[$cleanDbCif] ?? null;

            // Also try with RO prefix
            if (!$csvClient && !str_starts_with(strtoupper($dbCif), 'RO')) {
                $csvClient = $clientsInCsv['RO' . $cleanDbCif] ?? null;
            }

            if (!$csvClient) {
                $this->warn("  ⚠️  Not found in CSV");
                $notFound++;
                continue;
            }

            $this->line("  ✏️  Will update to: {$csvClient['name']}");

            if (!$isDryRun) {
                $client->update([
                    'name' => $csvClient['name'],
                    'company_name' => $csvClient['name'],
                    'notes' => 'Updated with real name from Smartbill CSV on ' . now()->format('Y-m-d H:i'),
                ]);
                $this->info("  ✅ Updated successfully");
            } else {
                $this->warn("  🔍 DRY RUN - Would update");
            }

            $updated++;
            $this->newLine();
        }

        // Summary
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('Summary:');
        $this->info("  ✅ Updated: {$updated}");
        $this->info("  ⚠️  Not found in CSV: {$notFound}");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        if (!$isDryRun && $updated > 0) {
            $this->newLine();
            $this->info('🎉 Client names updated successfully!');
            $this->info('You can now review the updated clients in your application.');
        }

        return 0;
    }
}
