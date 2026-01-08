<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Services\HtmlSanitizerService;
use Illuminate\Console\Command;

class SanitizeContracts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:sanitize
                          {--dry-run : Run without saving changes}
                          {--limit= : Limit number of records to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sanitize HTML content in all contracts to prevent XSS attacks';

    protected HtmlSanitizerService $sanitizer;

    /**
     * Execute the console command.
     */
    public function handle(HtmlSanitizerService $sanitizer): int
    {
        $this->sanitizer = $sanitizer;
        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit');

        $this->info('Starting contract sanitization...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be saved');
        }

        // Get all contracts (including soft deleted)
        $query = Contract::withTrashed();

        if ($limit) {
            $query->limit((int) $limit);
        }

        $contracts = $query->get();
        $total = $contracts->count();
        $sanitized = 0;
        $dangerous = 0;

        $this->info("Processing {$total} contracts...");
        $progressBar = $this->output->createProgressBar($total);

        foreach ($contracts as $contract) {
            $hadChanges = false;
            $hasDangerous = false;

            // Check and sanitize content
            if ($contract->content) {
                if ($this->sanitizer->containsDangerousContent($contract->content)) {
                    $hasDangerous = true;
                    $this->warn("\nContract #{$contract->id} contains dangerous content in content field");
                }
                $sanitized_content = $this->sanitizer->sanitize($contract->content);
                if ($sanitized_content !== $contract->content) {
                    $hadChanges = true;
                    if (!$dryRun) {
                        $contract->content = $sanitized_content;
                    }
                }
            }

            // Check and sanitize title
            if ($contract->title) {
                if ($this->sanitizer->containsDangerousContent($contract->title)) {
                    $hasDangerous = true;
                    $this->warn("\nContract #{$contract->id} contains dangerous content in title");
                }
                $sanitized_title = $this->sanitizer->sanitize($contract->title);
                if ($sanitized_title !== $contract->title) {
                    $hadChanges = true;
                    if (!$dryRun) {
                        $contract->title = $sanitized_title;
                    }
                }
            }

            // Check and sanitize blocks content
            if ($contract->blocks && is_array($contract->blocks)) {
                $blocks = $contract->blocks;
                foreach ($blocks as &$block) {
                    if (isset($block['data']['content'])) {
                        if ($this->sanitizer->containsDangerousContent($block['data']['content'])) {
                            $hasDangerous = true;
                            $this->warn("\nContract #{$contract->id} contains dangerous content in blocks");
                        }
                        $sanitized_block_content = $this->sanitizer->sanitize($block['data']['content']);
                        if ($sanitized_block_content !== $block['data']['content']) {
                            $hadChanges = true;
                            if (!$dryRun) {
                                $block['data']['content'] = $sanitized_block_content;
                            }
                        }
                    }

                    // Also sanitize text field if it exists
                    if (isset($block['data']['text'])) {
                        if ($this->sanitizer->containsDangerousContent($block['data']['text'])) {
                            $hasDangerous = true;
                            $this->warn("\nContract #{$contract->id} contains dangerous content in blocks text");
                        }
                        $sanitized_block_text = $this->sanitizer->sanitize($block['data']['text']);
                        if ($sanitized_block_text !== $block['data']['text']) {
                            $hadChanges = true;
                            if (!$dryRun) {
                                $block['data']['text'] = $sanitized_block_text;
                            }
                        }
                    }
                }
                if ($hadChanges && !$dryRun) {
                    $contract->blocks = $blocks;
                }
            }

            if ($hadChanges) {
                $sanitized++;
                if (!$dryRun) {
                    // Save without triggering events to avoid re-sanitization
                    $contract->saveQuietly();
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
                ['Total contracts processed', $total],
                ['Contracts with changes', $sanitized],
                ['Contracts with dangerous content', $dangerous],
            ]
        );

        if ($dryRun) {
            $this->warn('DRY RUN completed - no changes were saved');
            $this->info('Run without --dry-run to apply changes');
        }

        return Command::SUCCESS;
    }
}
