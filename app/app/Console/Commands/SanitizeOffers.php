<?php

namespace App\Console\Commands;

use App\Models\Offer;
use App\Services\HtmlSanitizerService;
use Illuminate\Console\Command;

class SanitizeOffers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:sanitize
                          {--dry-run : Run without saving changes}
                          {--limit= : Limit number of records to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sanitize HTML content in all offers to prevent XSS attacks';

    protected HtmlSanitizerService $sanitizer;

    /**
     * Execute the console command.
     */
    public function handle(HtmlSanitizerService $sanitizer): int
    {
        $this->sanitizer = $sanitizer;
        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit');

        $this->info('Starting offer sanitization...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be saved');
        }

        // Get all offers (including soft deleted)
        $query = Offer::withTrashed();

        if ($limit) {
            $query->limit((int) $limit);
        }

        $offers = $query->get();
        $total = $offers->count();
        $sanitized = 0;
        $dangerous = 0;

        $this->info("Processing {$total} offers...");
        $progressBar = $this->output->createProgressBar($total);

        foreach ($offers as $offer) {
            $hadChanges = false;
            $hasDangerous = false;

            // Check and sanitize introduction
            if ($offer->introduction) {
                if ($this->sanitizer->containsDangerousContent($offer->introduction)) {
                    $hasDangerous = true;
                    $this->warn("\nOffer #{$offer->id} contains dangerous content in introduction");
                }
                $sanitized_intro = $this->sanitizer->sanitize($offer->introduction);
                if ($sanitized_intro !== $offer->introduction) {
                    $hadChanges = true;
                    if (!$dryRun) {
                        $offer->introduction = $sanitized_intro;
                    }
                }
            }

            // Check and sanitize terms
            if ($offer->terms) {
                if ($this->sanitizer->containsDangerousContent($offer->terms)) {
                    $hasDangerous = true;
                    $this->warn("\nOffer #{$offer->id} contains dangerous content in terms");
                }
                $sanitized_terms = $this->sanitizer->sanitize($offer->terms);
                if ($sanitized_terms !== $offer->terms) {
                    $hadChanges = true;
                    if (!$dryRun) {
                        $offer->terms = $sanitized_terms;
                    }
                }
            }

            // Check and sanitize notes
            if ($offer->notes) {
                if ($this->sanitizer->containsDangerousContent($offer->notes)) {
                    $hasDangerous = true;
                    $this->warn("\nOffer #{$offer->id} contains dangerous content in notes");
                }
                $sanitized_notes = $this->sanitizer->sanitize($offer->notes);
                if ($sanitized_notes !== $offer->notes) {
                    $hadChanges = true;
                    if (!$dryRun) {
                        $offer->notes = $sanitized_notes;
                    }
                }
            }

            // Check and sanitize rejection_reason
            if ($offer->rejection_reason) {
                if ($this->sanitizer->containsDangerousContent($offer->rejection_reason)) {
                    $hasDangerous = true;
                    $this->warn("\nOffer #{$offer->id} contains dangerous content in rejection_reason");
                }
                $sanitized_reason = $this->sanitizer->sanitize($offer->rejection_reason);
                if ($sanitized_reason !== $offer->rejection_reason) {
                    $hadChanges = true;
                    if (!$dryRun) {
                        $offer->rejection_reason = $sanitized_reason;
                    }
                }
            }

            // Check and sanitize blocks content
            if ($offer->blocks && is_array($offer->blocks)) {
                $blocks = $offer->blocks;
                foreach ($blocks as &$block) {
                    if (isset($block['data']['content'])) {
                        if ($this->sanitizer->containsDangerousContent($block['data']['content'])) {
                            $hasDangerous = true;
                            $this->warn("\nOffer #{$offer->id} contains dangerous content in blocks");
                        }
                        $sanitized_content = $this->sanitizer->sanitize($block['data']['content']);
                        if ($sanitized_content !== $block['data']['content']) {
                            $hadChanges = true;
                            if (!$dryRun) {
                                $block['data']['content'] = $sanitized_content;
                            }
                        }
                    }
                }
                if ($hadChanges && !$dryRun) {
                    $offer->blocks = $blocks;
                }
            }

            if ($hadChanges) {
                $sanitized++;
                if (!$dryRun) {
                    // Save without triggering events to avoid re-sanitization
                    $offer->saveQuietly();
                }
            }

            if ($hasDangerous) {
                $dangerous++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Sanitization complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total offers processed', $total],
                ['Offers with changes', $sanitized],
                ['Offers with dangerous content', $dangerous],
            ]
        );

        if ($dryRun) {
            $this->warn('DRY RUN completed - no changes were saved');
            $this->info('Run without --dry-run to apply changes');
        }

        return Command::SUCCESS;
    }
}
