<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MigrateFinancialFileFolders extends Command
{
    protected $signature = 'financial:migrate-folders {--dry-run : Show what would be changed without making changes}';

    protected $description = 'Migrate financial file folders from "Ianuarie" to "01-Ianuarie" format';

    private array $monthMapping = [
        'Ianuarie' => '01-Ianuarie',
        'Februarie' => '02-Februarie',
        'Martie' => '03-Martie',
        'Aprilie' => '04-Aprilie',
        'Mai' => '05-Mai',
        'Iunie' => '06-Iunie',
        'Iulie' => '07-Iulie',
        'August' => '08-August',
        'Septembrie' => '09-Septembrie',
        'Octombrie' => '10-Octombrie',
        'Noiembrie' => '11-Noiembrie',
        'Decembrie' => '12-Decembrie',
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $disk = Storage::disk('financial');
        $basePath = $disk->path('');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Step 1: Rename physical folders
        $this->info('Step 1: Renaming physical folders...');
        $foldersRenamed = 0;

        $entries = scandir($basePath);
        $years = array_filter($entries, function ($d) use ($basePath) {
            return is_numeric($d) && is_dir("$basePath/$d");
        });

        foreach ($years as $year) {
            $yearPath = "$basePath/$year";
            foreach ($this->monthMapping as $old => $new) {
                $oldPath = "$yearPath/$old";
                $newPath = "$yearPath/$new";

                if (is_dir($oldPath) && !is_dir($newPath)) {
                    if ($dryRun) {
                        $this->line("  Would rename: <comment>$year/$old</comment> -> <info>$year/$new</info>");
                    } else {
                        rename($oldPath, $newPath);
                        $this->line("  Renamed: <comment>$year/$old</comment> -> <info>$year/$new</info>");
                    }
                    $foldersRenamed++;
                }
            }
        }

        if ($foldersRenamed === 0) {
            $this->line('  No folders to rename (already migrated or none exist)');
        } else {
            $this->info("  Total: $foldersRenamed folder(s) " . ($dryRun ? 'would be' : '') . " renamed");
        }

        $this->newLine();

        // Step 2: Update database records
        $this->info('Step 2: Updating database file_path records...');
        $files = DB::table('financial_files')->get(['id', 'file_path']);
        $updated = 0;

        foreach ($files as $file) {
            $newPath = $file->file_path;
            foreach ($this->monthMapping as $old => $new) {
                $newPath = str_replace("/$old/", "/$new/", $newPath);
            }

            if ($newPath !== $file->file_path) {
                if ($dryRun) {
                    $this->line("  Would update ID {$file->id}:");
                    $this->line("    <comment>{$file->file_path}</comment>");
                    $this->line("    <info>$newPath</info>");
                } else {
                    DB::table('financial_files')->where('id', $file->id)->update(['file_path' => $newPath]);
                }
                $updated++;
            }
        }

        if ($updated === 0) {
            $this->line('  No database records to update');
        } else {
            $this->info("  Total: $updated record(s) " . ($dryRun ? 'would be' : '') . " updated");
        }

        $this->newLine();

        if ($dryRun) {
            $this->warn('Dry run complete. No changes were made.');
            $this->line('Run without --dry-run to execute the migration.');
        } else {
            $this->info('Migration complete!');
        }

        return Command::SUCCESS;
    }
}
