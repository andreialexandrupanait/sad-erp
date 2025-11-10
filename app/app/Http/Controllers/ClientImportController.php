<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ClientImportController extends Controller
{
    /**
     * Show the import form
     */
    public function showImportForm()
    {
        return view('clients.import');
    }

    /**
     * Process the CSV import
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));

        // Get header row
        $header = array_shift($csvData);
        $header = array_map('trim', $header);

        // Get default status
        $defaultStatus = ClientSetting::active()->ordered()->first();

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($csvData as $index => $row) {
            $rowNumber = $index + 2; // +2 because we removed header and rows start at 1

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Map CSV columns to model attributes
            $data = array_combine($header, $row);

            // Validate required fields
            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'tax_id' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
            ]);

            if ($validator->fails()) {
                $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                $skipped++;
                continue;
            }

            // Check for duplicate tax_id for this user
            if (!empty($data['tax_id'])) {
                $exists = Client::where('tax_id', trim($data['tax_id']))
                    ->where('user_id', auth()->id())
                    ->exists();

                if ($exists) {
                    $errors[] = "Row {$rowNumber}: Client with tax_id '{$data['tax_id']}' already exists";
                    $skipped++;
                    continue;
                }
            }

            try {
                // Prepare client data
                $clientData = [
                    'name' => trim($data['name'] ?? ''),
                    'company_name' => trim($data['company_name'] ?? $data['company'] ?? ''),
                    'tax_id' => trim($data['tax_id'] ?? $data['cui'] ?? ''),
                    'registration_number' => trim($data['registration_number'] ?? $data['reg_number'] ?? ''),
                    'contact_person' => trim($data['contact_person'] ?? ''),
                    'email' => trim($data['email'] ?? ''),
                    'phone' => trim($data['phone'] ?? ''),
                    'address' => trim($data['address'] ?? ''),
                    'vat_payer' => in_array(strtolower(trim($data['vat_payer'] ?? '')), ['yes', 'da', '1', 'true']),
                    'notes' => trim($data['notes'] ?? ''),
                    'status_id' => $defaultStatus ? $defaultStatus->id : null,
                ];

                // Create client
                Client::create($clientData);
                $imported++;

            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $skipped++;
            }
        }

        $message = "Import completed: {$imported} clients imported";
        if ($skipped > 0) {
            $message .= ", {$skipped} skipped";
        }

        return redirect()
            ->route('clients.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="clients_import_template.csv"',
        ];

        $columns = [
            'name',
            'company_name',
            'tax_id',
            'registration_number',
            'contact_person',
            'email',
            'phone',
            'address',
            'vat_payer',
            'notes',
        ];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            // Add example row
            fputcsv($file, [
                'John Doe',
                'Example Company SRL',
                'RO12345678',
                'J40/1234/2020',
                'Jane Smith',
                'contact@example.com',
                '+40 123 456 789',
                'Str. Example nr. 1, Bucharest',
                'yes',
                'Example notes',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export clients to CSV
     */
    public function export(Request $request)
    {
        $query = Client::with('status');

        // Apply same filters as index page
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status_id')) {
            $query->byStatus($request->status_id);
        }

        $clients = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="clients_export_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($clients) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'ID',
                'Name',
                'Company Name',
                'Tax ID',
                'Registration Number',
                'Contact Person',
                'Email',
                'Phone',
                'Address',
                'VAT Payer',
                'Status',
                'Notes',
                'Created At',
            ]);

            // Data rows
            foreach ($clients as $client) {
                fputcsv($file, [
                    $client->id,
                    $client->name,
                    $client->company_name,
                    $client->tax_id,
                    $client->registration_number,
                    $client->contact_person,
                    $client->email,
                    $client->phone,
                    $client->address,
                    $client->vat_payer ? 'Yes' : 'No',
                    $client->status ? $client->status->name : '',
                    $client->notes,
                    $client->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
