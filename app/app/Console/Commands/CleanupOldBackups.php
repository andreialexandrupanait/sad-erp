<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupOldBackups extends Command
{
    protected $signature = 'backup:cleanup
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Clean up old backups according to retention policy';

    protected string $backupPath;

    // Retention policy
    protected int $keepDaily = 7;      // Keep last 7 daily backups
    protected int $keepWeekly = 4;     // Keep last 4 weekly backups
    protected int $keepMonthly = 3;    // Keep last 3 monthly backups
    protected int $maxAgeDays = 90;    // Delete anything older than 90 days

    public function __construct()
    {
        parent::__construct();
        // Use /var/www/backups in Docker, or ../backups locally
        $dockerPath = '/var/www/backups/database';
        $localPath = base_path('../backups/database');
        $this->backupPath = is_dir('/var/www/backups') ? $dockerPath : $localPath;
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn("DRY RUN MODE - No files will be deleted");
        }

        $this->info("Starting backup cleanup...");
        $this->info("Retention policy:");
        $this->info("  - Keep {$this->keepDaily} daily backups");
        $this->info("  - Keep {$this->keepWeekly} weekly backups");
        $this->info("  - Keep {$this->keepMonthly} monthly backups");
        $this->info("  - Delete files older than {$this->maxAgeDays} days");
        $this->newLine();

        $totalDeleted = 0;
        $totalFreed = 0;

        // Clean each backup type
        foreach (['daily', 'weekly', 'monthly'] as $type) {
            $result = $this->cleanupType($type, $dryRun);
            $totalDeleted += $result['deleted'];
            $totalFreed += $result['freed'];
        }

        // Also clean up old file backups
        $result = $this->cleanupFileBackups($dryRun);
        $totalDeleted += $result['deleted'];
        $totalFreed += $result['freed'];

        $this->newLine();
        $this->info("Cleanup complete!");
        $this->info("  Files deleted: {$totalDeleted}");
        $this->info("  Space freed: " . $this->formatBytes($totalFreed));

        Log::info("Backup cleanup completed", [
            'files_deleted' => $totalDeleted,
            'space_freed' => $totalFreed,
            'dry_run' => $dryRun,
        ]);

        return Command::SUCCESS;
    }

    protected function cleanupType(string $type, bool $dryRun): array
    {
        $dir = "{$this->backupPath}/{$type}";
        $deleted = 0;
        $freed = 0;

        if (!is_dir($dir)) {
            return ['deleted' => 0, 'freed' => 0];
        }

        $this->info("Cleaning {$type} backups in {$dir}...");

        // Get all backup files sorted by modification time (newest first)
        $files = glob("{$dir}/backup_*.sql*");
        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        $keepCount = match($type) {
            'daily' => $this->keepDaily,
            'weekly' => $this->keepWeekly,
            'monthly' => $this->keepMonthly,
            default => 7,
        };

        $cutoffDate = Carbon::now()->subDays($this->maxAgeDays);

        foreach ($files as $index => $file) {
            $fileDate = Carbon::createFromTimestamp(filemtime($file));
            $filename = basename($file);
            $filesize = filesize($file);

            // Keep if within retention count and not too old
            if ($index < $keepCount && $fileDate->gt($cutoffDate)) {
                $this->line("  [KEEP] {$filename} (" . $this->formatBytes($filesize) . ")");
                continue;
            }

            // Delete if beyond retention count OR too old
            $reason = $index >= $keepCount ? "exceeds count ({$keepCount})" : "older than {$this->maxAgeDays} days";

            if ($dryRun) {
                $this->warn("  [WOULD DELETE] {$filename} - {$reason}");
            } else {
                if (unlink($file)) {
                    $this->line("  [DELETED] {$filename} - {$reason}");
                    $deleted++;
                    $freed += $filesize;
                } else {
                    $this->error("  [FAILED] Could not delete {$filename}");
                }
            }
        }

        return ['deleted' => $deleted, 'freed' => $freed];
    }

    protected function cleanupFileBackups(bool $dryRun): array
    {
        $dockerPath = '/var/www/backups/files';
        $localPath = base_path('../backups/files');
        $dir = is_dir('/var/www/backups') ? $dockerPath : $localPath;
        $deleted = 0;
        $freed = 0;

        if (!is_dir($dir)) {
            return ['deleted' => 0, 'freed' => 0];
        }

        $this->info("Cleaning file backups in {$dir}...");

        $cutoffDate = Carbon::now()->subDays($this->maxAgeDays);
        $files = glob("{$dir}/*.tar.gz");

        foreach ($files as $file) {
            $fileDate = Carbon::createFromTimestamp(filemtime($file));
            $filename = basename($file);
            $filesize = filesize($file);

            if ($fileDate->gt($cutoffDate)) {
                $this->line("  [KEEP] {$filename} (" . $this->formatBytes($filesize) . ")");
                continue;
            }

            if ($dryRun) {
                $this->warn("  [WOULD DELETE] {$filename} - older than {$this->maxAgeDays} days");
            } else {
                if (unlink($file)) {
                    $this->line("  [DELETED] {$filename}");
                    $deleted++;
                    $freed += $filesize;
                } else {
                    $this->error("  [FAILED] Could not delete {$filename}");
                }
            }
        }

        return ['deleted' => $deleted, 'freed' => $freed];
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
