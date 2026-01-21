<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\ClientNote;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportClientNotesCommand extends Command
{
    protected $signature = 'notes:import {file : Path to the text file with messages} {--force : Skip confirmation}';
    protected $description = 'Import client notes/messages from a text file';

    protected $clients = [];
    protected $clientsByCompany = [];
    protected $unmatchedClientNames = [];

    // Pre-built regex pattern for O(1) client matching instead of O(n) loops
    protected $clientSearchPattern = '';
    protected $clientTermToId = [];

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("  Client Notes Import");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("  File: " . basename($filePath));
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->newLine();

        // Get the first user (admin) to assign notes to
        $user = User::first();

        if (!$user) {
            $this->error("No users found in database. Please create a user first.");
            return 1;
        }

        $this->info("Assigning notes to user: {$user->name} (ID: {$user->id})");
        $this->info("Organization: {$user->organization_id}");
        $this->newLine();

        // Load all clients for matching
        $allClients = Client::withoutGlobalScopes()
            ->where('organization_id', $user->organization_id)
            ->get();

        $this->clients = $allClients->keyBy(function ($client) {
            return Str::lower($client->name);
        });

        // Also index by company name for additional matching
        $this->clientsByCompany = $allClients
            ->filter(fn($c) => !empty($c->company_name))
            ->keyBy(function ($client) {
                return Str::lower($client->company_name);
            });

        $this->info("Loaded {$this->clients->count()} clients for matching");
        $this->info("Loaded {$this->clientsByCompany->count()} company names for matching");

        // Build single multi-term regex pattern for O(1) matching instead of O(n) loops per block
        $this->buildClientSearchPattern();
        $this->info("Built search pattern with " . count($this->clientTermToId) . " searchable terms");
        $this->newLine();

        // Read and parse file
        $content = file_get_contents($filePath);

        // Split by lines of dashes (4 or more dashes)
        $blocks = preg_split('/\n-{4,}\n/', $content);

        $this->info("Found " . count($blocks) . " message blocks");
        $this->newLine();

        if (!$this->option('force') && !$this->confirm('Do you want to proceed with the import?')) {
            $this->info('Import cancelled.');
            return 0;
        }

        $bar = $this->output->createProgressBar(count($blocks));
        $bar->start();

        $imported = 0;
        $withClient = 0;
        $withoutClient = 0;
        $skipped = 0;

        foreach ($blocks as $block) {
            $block = trim($block);

            // Skip empty blocks
            if (empty($block) || strlen($block) < 10) {
                $skipped++;
                $bar->advance();
                continue;
            }

            // Try to detect client name from first line
            $clientId = $this->detectClient($block);

            // Convert plain text to HTML (preserve line breaks)
            $htmlContent = $this->textToHtml($block);

            // Create the note
            $note = new ClientNote();
            $note->organization_id = $user->organization_id;
            $note->user_id = $user->id;
            $note->client_id = $clientId;
            $note->content = $htmlContent;
            $note->tags = ClientNote::extractTags($block);
            $note->save();

            $imported++;
            if ($clientId) {
                $withClient++;
            } else {
                $withoutClient++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("  Import Summary");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("  Total imported: {$imported}");
        $this->info("  With client: {$withClient}");
        $this->info("  Without client: {$withoutClient}");
        $this->info("  Skipped (empty): {$skipped}");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        if (!empty($this->unmatchedClientNames)) {
            $this->newLine();
            $this->warn("Unmatched client names detected in messages:");
            $uniqueNames = array_unique($this->unmatchedClientNames);
            sort($uniqueNames);
            foreach ($uniqueNames as $name) {
                $this->line("  - {$name}");
            }
        }

        return 0;
    }

    /**
     * Build a single regex pattern with all client names for fast matching.
     * This reduces O(n*m) to O(n) where n is blocks and m is clients.
     */
    protected function buildClientSearchPattern(): void
    {
        $terms = [];

        // Collect all client names >= 4 chars
        foreach ($this->clients as $lowerName => $client) {
            if (strlen($lowerName) >= 4) {
                $terms[$lowerName] = $client->id;
            }
        }

        // Also collect company names
        foreach ($this->clientsByCompany as $lowerCompanyName => $client) {
            if (strlen($lowerCompanyName) >= 4) {
                $terms[$lowerCompanyName] = $client->id;
            }
        }

        if (empty($terms)) {
            $this->clientSearchPattern = '';
            return;
        }

        // Sort by length descending so longer matches come first in the pattern
        uksort($terms, fn($a, $b) => strlen($b) <=> strlen($a));

        $this->clientTermToId = $terms;

        // Build single regex pattern with all terms (longest first for priority)
        $escapedTerms = array_map(fn($t) => preg_quote($t, '/'), array_keys($terms));
        $this->clientSearchPattern = '/(' . implode('|', $escapedTerms) . ')/i';
    }

    /**
     * Try to detect a client name from the message block.
     * Uses pre-built regex pattern for O(1) matching instead of O(n) loops.
     */
    protected function detectClient(string $block): ?int
    {
        // If no pattern was built, no clients to match
        if (empty($this->clientSearchPattern)) {
            return null;
        }

        // Single regex match finds the first (longest due to pattern order) client name
        if (preg_match($this->clientSearchPattern, $block, $matches)) {
            $matchedTerm = strtolower($matches[1]);
            return $this->clientTermToId[$matchedTerm] ?? null;
        }

        return null;
    }

    /**
     * Find a client by name (fuzzy matching)
     */
    protected function findClient(string $name): ?Client
    {
        $lowerName = Str::lower(trim($name));

        // Exact match
        if (isset($this->clients[$lowerName])) {
            return $this->clients[$lowerName];
        }

        // Partial match - check if any client name contains this name or vice versa
        foreach ($this->clients as $clientLowerName => $client) {
            if (strlen($lowerName) > 3 && strlen($clientLowerName) > 3) {
                if (Str::contains($clientLowerName, $lowerName) || Str::contains($lowerName, $clientLowerName)) {
                    return $client;
                }
            }
        }

        return null;
    }

    /**
     * Convert plain text to HTML
     */
    protected function textToHtml(string $text): string
    {
        // Escape HTML entities
        $html = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // Convert URLs to links
        $html = preg_replace(
            '/(https?:\/\/[^\s\[\]<>]+)/i',
            '<a href="$1" target="_blank">$1</a>',
            $html
        );

        // Convert line breaks to <br> or paragraphs
        $html = nl2br($html);

        return $html;
    }
}
