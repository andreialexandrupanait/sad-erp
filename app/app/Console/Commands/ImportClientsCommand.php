<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportClientsCommand extends Command
{
    protected $signature = 'import:clients {file=clienti_2025-11-11.csv}';
    protected $description = 'Import clients from CSV file';

    public function handle()
    {
        $filePath = storage_path('app/imports/' . $this->argument('file'));

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("  Client Import Process");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("  File: " . basename($filePath));
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->newLine();

        // Get the first user (admin) to assign clients to
        $user = User::first();

        if (!$user) {
            $this->error("No users found in database. Please create a user first.");
            return 1;
        }

        $this->info("Assigning clients to user: {$user->name} (ID: {$user->id})");
        $this->newLine();

        // Read CSV file
        $csvData = array_map(function($line) {
            return str_getcsv($line, ',', '"');
        }, file($filePath));

        // Get headers from first row
        $headers = array_shift($csvData);

        $this->info("CSV Headers: " . implode(', ', $headers));
        $this->info("Total rows to import: " . count($csvData));
        $this->newLine();

        // Status mapping
        $statusMap = [
            'Mentenanta' => 'Mentenanță',
            'In progress' => 'In Progress',
            'Canceled' => 'Canceled',
            'Supraveghere' => 'Supraveghere',
        ];

        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            $progressBar = $this->output->createProgressBar(count($csvData));
            $progressBar->start();

            foreach ($csvData as $index => $row) {
                $progressBar->advance();

                // Skip empty rows
                if (empty(array_filter($row))) {
                    $skipped++;
                    continue;
                }

                // Map CSV columns to array
                $data = array_combine($headers, $row);

                // Prepare client data
                $name = trim($data['Nume'] ?? '');
                $companyName = trim($data['Companie'] ?? '');
                $taxId = trim($data['CUI'] ?? '');
                $registrationNumber = trim($data['Nr. Înregistrare'] ?? '');
                $address = trim($data['Adresă'] ?? '');
                $email = trim($data['Email'] ?? '');
                $phone = trim($data['Telefon'] ?? '');
                $contactPerson = trim($data['Persoană Contact'] ?? '');
                $status = trim($data['Status'] ?? '');
                $vatPayer = trim($data['Plătitor TVA'] ?? 'Nu');
                $notes = trim($data['Notițe'] ?? '');

                // Skip if no name
                if (empty($name)) {
                    $skipped++;
                    continue;
                }

                // Generate slug
                $slug = Str::slug($name);

                // Ensure unique slug
                $originalSlug = $slug;
                $counter = 1;
                while (Client::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                // Map status
                $mappedStatus = $statusMap[$status] ?? null;

                try {
                    Client::create([
                        'user_id' => $user->id,
                        'name' => $name,
                        'company_name' => $companyName ?: null,
                        'slug' => $slug,
                        'tax_id' => $taxId ?: null,
                        'registration_number' => $registrationNumber ?: null,
                        'contact_person' => $contactPerson ?: null,
                        'email' => $email ?: null,
                        'phone' => $phone ?: null,
                        'address' => $address ?: null,
                        'vat_payer' => $vatPayer === 'Da' ? 1 : 0,
                        'notes' => $notes ?: null,
                        'status_id' => null, // Status will be set via client_settings if needed
                        'order_index' => $imported,
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $index + 2, // +2 because of header and 0-index
                        'name' => $name,
                        'error' => $e->getMessage(),
                    ];
                    $skipped++;
                }
            }

            $progressBar->finish();
            $this->newLine(2);

            DB::commit();

            // Display results
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("  Import Complete!");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("  ✓ Successfully imported: {$imported} clients");

            if ($skipped > 0) {
                $this->warn("  ⚠ Skipped: {$skipped} rows");
            }

            if (count($errors) > 0) {
                $this->error("  ✗ Errors: " . count($errors));
                $this->newLine();
                $this->error("Error details:");
                foreach ($errors as $error) {
                    $this->error("  Row {$error['row']} ({$error['name']}): {$error['error']}");
                }
            }

            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->newLine();

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Import failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
