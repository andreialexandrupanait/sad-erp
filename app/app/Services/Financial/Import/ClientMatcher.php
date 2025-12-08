<?php

namespace App\Services\Financial\Import;

use App\Models\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Client Matcher Service - Handles client lookup and creation during imports
 */
class ClientMatcher
{
    protected array $clientsByCif = [];
    protected Collection $clientsByName;
    public array $stats = ['clients_created' => 0, 'clients_updated' => 0];

    public function loadIndex(): void
    {
        $clients = Client::all();
        $this->clientsByCif = [];

        foreach ($clients as $client) {
            if (!empty($client->tax_id)) {
                $this->clientsByCif[$client->tax_id] = $client;
                $cleanCif = preg_replace('/^RO/i', '', $client->tax_id);
                $this->clientsByCif[$cleanCif] = $client;
                $this->clientsByCif['RO' . $cleanCif] = $client;
            }
        }

        $this->clientsByName = $clients->keyBy(fn($c) => strtolower($c->name));
    }

    public function findByCif(string $cif): ?Client
    {
        $cleanCif = preg_replace('/^RO/i', '', trim($cif));
        return $this->clientsByCif[$cif] ?? $this->clientsByCif[$cleanCif] ?? $this->clientsByCif['RO' . $cleanCif] ?? null;
    }

    public function findByName(string $name): ?Client
    {
        return $this->clientsByName[strtolower(trim($name))] ?? null;
    }

    public function findOrCreate(array $data, bool $dryRun = false): ?int
    {
        $cif = trim($data['cif_client'] ?? '');
        $name = trim($data['client_name'] ?? '');

        if (empty($cif) && empty($name)) return null;

        // Try CIF match
        if (!empty($cif)) {
            $client = $this->findByCif($cif);
            if ($client) return $client->id;

            // Create new
            if (!empty($name)) {
                return $this->create($name, $cif, $data, $dryRun);
            }
        }

        // Fallback to name
        if (!empty($name)) {
            $client = $this->findByName($name);
            if ($client) return $client->id;
        }

        return null;
    }

    protected function create(string $name, string $cif, array $data, bool $dryRun): ?int
    {
        if ($dryRun) {
            Log::info('DRY RUN: Would create client', ['name' => $name, 'cif' => $cif]);
            return null;
        }

        try {
            $client = Client::create([
                'name' => mb_convert_case(trim($name), MB_CASE_TITLE),
                'company_name' => mb_convert_case(trim($name), MB_CASE_TITLE),
                'tax_id' => $cif,
                'address' => $data['client_address'] ?? null,
                'contact_person' => $data['client_contact'] ?? null,
                'notes' => 'Auto-created from import on ' . now()->format('Y-m-d H:i'),
            ]);

            // Update index
            $this->clientsByCif[$cif] = $client;
            $this->clientsByName[strtolower($client->name)] = $client;
            $this->stats['clients_created']++;

            Log::info('Auto-created client', ['id' => $client->id, 'name' => $client->name]);
            return $client->id;
        } catch (\Exception $e) {
            Log::warning('Failed to create client', ['name' => $name, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
