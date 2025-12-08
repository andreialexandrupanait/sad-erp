<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;

class ConvertClientNamesToTitleCaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:convert-to-title-case
                            {--dry-run : Preview changes without applying them}
                            {--all : Convert all clients, not just ALL CAPS ones}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert ALL CAPS client names to Title Case';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $convertAll = $this->option('all');

        $this->info('🔍 Searching for clients with ALL CAPS names...');
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        $this->newLine();

        // Get clients with ALL CAPS names
        $query = Client::withoutGlobalScope('user_scope');

        if (!$convertAll) {
            // Only get clients where name is ALL CAPS
            $query->whereRaw('BINARY name = UPPER(name)')
                  ->whereRaw('name != LOWER(name)') // Exclude all lowercase
                  ->whereRaw('CHAR_LENGTH(name) > 3'); // Exclude very short names
        }

        $clients = $query->get();

        if ($clients->isEmpty()) {
            $this->info('✅ No ALL CAPS client names found!');
            return 0;
        }

        $this->info("Found {$clients->count()} client(s) to convert");
        $this->newLine();

        $converted = 0;
        $skipped = 0;

        foreach ($clients as $client) {
            $oldName = $client->name;
            $newName = $this->formatClientName($oldName);

            // Skip if no change needed
            if ($oldName === $newName) {
                $skipped++;
                continue;
            }

            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->line("Client ID: {$client->id}");
            $this->line("  Old: {$oldName}");
            $this->line("  New: {$newName}");

            if ($isDryRun) {
                $this->warn("  🔍 DRY RUN - Would update");
            } else {
                try {
                    $client->update([
                        'name' => $newName,
                        'company_name' => $newName,
                    ]);
                    $this->info("  ✅ Updated");
                    $converted++;
                } catch (\Exception $e) {
                    $this->error("  ❌ Error: {$e->getMessage()}");
                    $skipped++;
                }
            }

            $this->newLine();
        }

        // Summary
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('Summary:');
        if ($isDryRun) {
            $this->info("  📊 Would convert: {$clients->count()}");
        } else {
            $this->info("  ✅ Converted: {$converted}");
            $this->info("  ⏭️  Skipped: {$skipped}");
        }
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return 0;
    }

    /**
     * Format client name to Title Case
     */
    protected function formatClientName($name)
    {
        if (empty($name)) {
            return $name;
        }

        // Convert to Title Case using multibyte string function
        return mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8');
    }
}
