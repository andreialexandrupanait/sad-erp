<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FinancialRevenue;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateRevenuesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smartbill:cleanup-duplicates
                            {--dry-run : Run without making changes}
                            {--auto : Automatically choose which duplicate to keep without prompting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate revenues created during Smartbill import';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isAuto = $this->option('auto');

        $this->info('🔍 Searching for duplicate revenues...');
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        $this->newLine();

        // Find duplicates by smartbill_series + smartbill_invoice_number
        $duplicates = FinancialRevenue::withoutGlobalScope('user_scope')
            ->select('smartbill_series', 'smartbill_invoice_number', DB::raw('COUNT(*) as count'))
            ->whereNotNull('smartbill_series')
            ->whereNotNull('smartbill_invoice_number')
            ->groupBy('smartbill_series', 'smartbill_invoice_number')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('✅ No duplicates found. Your data is clean!');
            return 0;
        }

        $this->info("Found {$duplicates->count()} invoice(s) with duplicates");
        $this->newLine();

        $totalDeleted = 0;
        $totalKept = 0;

        foreach ($duplicates as $duplicate) {
            $series = $duplicate->smartbill_series;
            $number = $duplicate->smartbill_invoice_number;
            $count = $duplicate->count;

            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("Invoice: {$series}-{$number} ({$count} copies)");

            // Get all instances of this duplicate - eager load relationships to avoid N+1
            $revenues = FinancialRevenue::withoutGlobalScope('user_scope')
                ->where('smartbill_series', $series)
                ->where('smartbill_invoice_number', $number)
                ->with(['files', 'client'])
                ->withCount('files')
                ->orderBy('id')
                ->get();

            // Display all copies
            foreach ($revenues as $index => $revenue) {
                $hasFiles = $revenue->files_count > 0;
                $fileInfo = $hasFiles ? " [HAS FILES]" : "";
                $clientInfo = $revenue->client_id ? " Client: " . ($revenue->client->name ?? 'Unknown') : " [NO CLIENT]";

                $this->line("  [{$index}] ID: {$revenue->id} | Created: {$revenue->created_at} | Amount: {$revenue->amount} {$revenue->currency}{$clientInfo}{$fileInfo}");
            }

            // Determine which to keep
            if ($isAuto) {
                // Auto mode: keep the one with files, or the oldest one
                $toKeep = $revenues->first(function ($r) {
                    return $r->files_count > 0;
                }) ?? $revenues->first();

                $this->warn("  → Auto-keeping: ID {$toKeep->id} (has files: " . ($toKeep->files_count > 0 ? 'yes' : 'no') . ")");
            } else {
                // Interactive mode
                $choice = $this->ask("Which one should we KEEP? Enter the index [0-" . ($revenues->count() - 1) . "], or 's' to skip", '0');

                if ($choice === 's') {
                    $this->warn("  ⏭️  Skipped");
                    $this->newLine();
                    continue;
                }

                $toKeep = $revenues->get($choice);
                if (!$toKeep) {
                    $this->error("  ❌ Invalid choice. Skipping.");
                    $this->newLine();
                    continue;
                }
            }

            // Delete the others
            $toDelete = $revenues->reject(fn($r) => $r->id === $toKeep->id);

            foreach ($toDelete as $revenue) {
                $this->line("  🗑️  Deleting ID: {$revenue->id}");

                if (!$isDryRun) {
                    // Check if it has files (using pre-loaded count)
                    if ($revenue->files_count > 0) {
                        $this->warn("    ⚠️  This revenue has {$revenue->files_count} file(s). They will be deleted too.");

                        // Files will be auto-deleted by FinancialFile model's deleted event
                    }

                    $revenue->delete();
                    $this->info("    ✅ Deleted");
                } else {
                    $this->warn("    🔍 DRY RUN - Would delete");
                }

                $totalDeleted++;
            }

            $this->info("  ✅ Kept ID: {$toKeep->id}");
            $totalKept++;
            $this->newLine();
        }

        // Summary
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('Summary:');
        $this->info("  ✅ Invoices kept: {$totalKept}");
        $this->info("  🗑️  Revenues deleted: {$totalDeleted}");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        if (!$isDryRun && $totalDeleted > 0) {
            $this->newLine();
            $this->info('🎉 Duplicate cleanup completed successfully!');
        }

        return 0;
    }
}
