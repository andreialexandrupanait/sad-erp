<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\ContractAnnex;
use App\Models\FinancialFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateFilesToR2 extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'storage:migrate-r2
                            {--type=all : Type to migrate (financial|contracts|all)}
                            {--dry-run : Preview without actually migrating}
                            {--batch=100 : Number of files per batch}';

    /**
     * The console command description.
     */
    protected $description = 'Migrate existing files from local storage to Cloudflare R2';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');
        $batch = (int) $this->option('batch');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files will be migrated');
        }

        $this->info('Starting migration to R2...');
        $this->newLine();

        $totalMigrated = 0;
        $totalFailed = 0;

        if (in_array($type, ['financial', 'all'])) {
            [$migrated, $failed] = $this->migrateFinancialFiles($dryRun, $batch);
            $totalMigrated += $migrated;
            $totalFailed += $failed;
        }

        if (in_array($type, ['contracts', 'all'])) {
            [$migrated, $failed] = $this->migrateContractPdfs($dryRun, $batch);
            $totalMigrated += $migrated;
            $totalFailed += $failed;

            [$migrated, $failed] = $this->migrateAnnexPdfs($dryRun, $batch);
            $totalMigrated += $migrated;
            $totalFailed += $failed;
        }

        $this->newLine();
        $this->info("Migration complete!");
        $this->info("Total migrated: {$totalMigrated}");
        if ($totalFailed > 0) {
            $this->warn("Total failed: {$totalFailed}");
        }

        return Command::SUCCESS;
    }

    /**
     * Migrate financial files.
     */
    protected function migrateFinancialFiles(bool $dryRun, int $batch): array
    {
        $this->info('Migrating financial files...');

        $localDisk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('app/financial_files'),
        ]);
        $r2Disk = Storage::disk('r2');

        $migrated = 0;
        $failed = 0;

        FinancialFile::chunk($batch, function ($files) use ($localDisk, $r2Disk, $dryRun, &$migrated, &$failed) {
            foreach ($files as $file) {
                $sourcePath = $file->file_path;
                $targetPath = 'financial/' . $file->file_path;

                if (!$localDisk->exists($sourcePath)) {
                    $this->warn("Missing: {$sourcePath}");
                    $failed++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("Would migrate: {$sourcePath} -> {$targetPath}");
                    $migrated++;
                    continue;
                }

                try {
                    $r2Disk->put($targetPath, $localDisk->get($sourcePath));
                    $this->info("Migrated: {$targetPath}");
                    $migrated++;
                } catch (\Exception $e) {
                    $this->error("Failed: {$sourcePath} - " . $e->getMessage());
                    $failed++;
                }
            }
        });

        $this->info("Financial files: {$migrated} migrated, {$failed} failed");

        return [$migrated, $failed];
    }

    /**
     * Migrate contract PDFs.
     */
    protected function migrateContractPdfs(bool $dryRun, int $batch): array
    {
        $this->info('Migrating contract PDFs...');

        $localDisk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('app'),
        ]);
        $r2Disk = Storage::disk('r2');

        $migrated = 0;
        $failed = 0;

        Contract::whereNotNull('pdf_path')->chunk($batch, function ($contracts) use ($localDisk, $r2Disk, $dryRun, &$migrated, &$failed) {
            foreach ($contracts as $contract) {
                $sourcePath = $contract->pdf_path;
                $targetPath = $contract->pdf_path;

                if (!$localDisk->exists($sourcePath)) {
                    $this->warn("Missing: {$sourcePath}");
                    $failed++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("Would migrate: {$sourcePath}");
                    $migrated++;
                    continue;
                }

                try {
                    $r2Disk->put($targetPath, $localDisk->get($sourcePath));
                    $this->info("Migrated: {$targetPath}");
                    $migrated++;
                } catch (\Exception $e) {
                    $this->error("Failed: {$sourcePath} - " . $e->getMessage());
                    $failed++;
                }
            }
        });

        $this->info("Contract PDFs: {$migrated} migrated, {$failed} failed");

        return [$migrated, $failed];
    }

    /**
     * Migrate contract annex PDFs.
     */
    protected function migrateAnnexPdfs(bool $dryRun, int $batch): array
    {
        $this->info('Migrating contract annex PDFs...');

        $localDisk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('app'),
        ]);
        $r2Disk = Storage::disk('r2');

        $migrated = 0;
        $failed = 0;

        ContractAnnex::whereNotNull('pdf_path')->chunk($batch, function ($annexes) use ($localDisk, $r2Disk, $dryRun, &$migrated, &$failed) {
            foreach ($annexes as $annex) {
                $sourcePath = $annex->pdf_path;
                $targetPath = $annex->pdf_path;

                if (!$localDisk->exists($sourcePath)) {
                    $this->warn("Missing: {$sourcePath}");
                    $failed++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("Would migrate: {$sourcePath}");
                    $migrated++;
                    continue;
                }

                try {
                    $r2Disk->put($targetPath, $localDisk->get($sourcePath));
                    $this->info("Migrated: {$targetPath}");
                    $migrated++;
                } catch (\Exception $e) {
                    $this->error("Failed: {$sourcePath} - " . $e->getMessage());
                    $failed++;
                }
            }
        });

        $this->info("Annex PDFs: {$migrated} migrated, {$failed} failed");

        return [$migrated, $failed];
    }
}
