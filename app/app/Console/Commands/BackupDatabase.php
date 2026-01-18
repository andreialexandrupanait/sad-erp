<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database
                            {--compress : Compress the backup with gzip}
                            {--include-files : Also backup uploaded files}
                            {--type=daily : Backup type (daily|weekly|monthly|manual)}';

    protected $description = 'Create automated database backup using mysqldump';

    protected string $backupPath;

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
        $startTime = microtime(true);
        $type = $this->option('type');
        $compress = $this->option('compress');
        $includeFiles = $this->option('include-files');

        $this->info("Starting {$type} backup...");

        // Ensure backup directories exist
        $this->ensureDirectoriesExist();

        // Generate filename with timestamp
        $timestamp = now()->format('Y-m-d_His');
        $filename = "backup_{$type}_{$timestamp}.sql";
        $filepath = "{$this->backupPath}/{$type}/{$filename}";

        // Get database credentials
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        // Build mysqldump command
        // --ssl=0 disables SSL which can cause issues in Docker
        // --no-tablespaces avoids needing PROCESS privilege
        // SECURITY: Password passed via MYSQL_PWD environment variable instead of command line
        // to prevent exposure in process lists (ps aux)
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --ssl=0 --no-tablespaces --single-transaction --routines --triggers %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database)
        );

        if ($compress) {
            $filepath .= '.gz';
            $command .= ' | gzip';
        }

        $command .= ' > ' . escapeshellarg($filepath);

        // Execute backup using Symfony Process for better security and error handling
        $this->info("Executing mysqldump to {$filepath}...");

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(600); // 10 minutes max for large databases
        // Pass password via environment variable for security (not visible in process list)
        $process->setEnv(['MYSQL_PWD' => $password]);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $error = $process->getErrorOutput() ?: $process->getOutput();
            $this->error("Backup failed: {$error}");
            Log::error("Database backup failed", [
                'type' => $type,
                'error' => $error,
                'return_code' => $process->getExitCode(),
            ]);
            return Command::FAILURE;
        }

        // Verify backup
        if (!file_exists($filepath)) {
            $this->error("Backup file was not created!");
            Log::error("Database backup file not created", ['filepath' => $filepath]);
            return Command::FAILURE;
        }

        $filesize = filesize($filepath);
        if ($filesize < 1000) { // Less than 1KB is suspicious
            $this->warn("Warning: Backup file is suspiciously small ({$filesize} bytes)");
        }

        // Verify gzip integrity if compressed
        if ($compress) {
            $gzipProcess = new Process(['gzip', '-t', $filepath]);
            $gzipProcess->run();
            if (!$gzipProcess->isSuccessful()) {
                $this->error("Backup file is corrupted!");
                Log::error("Backup gzip verification failed", ['filepath' => $filepath]);
                return Command::FAILURE;
            }
        }

        $duration = round(microtime(true) - $startTime, 2);
        $filesizeFormatted = $this->formatBytes($filesize);

        $this->info("Database backup completed successfully!");
        $this->info("  File: {$filepath}");
        $this->info("  Size: {$filesizeFormatted}");
        $this->info("  Duration: {$duration}s");

        Log::info("Database backup completed", [
            'type' => $type,
            'filepath' => $filepath,
            'filesize' => $filesize,
            'duration' => $duration,
            'compressed' => $compress,
        ]);

        // Backup uploaded files if requested
        if ($includeFiles) {
            $this->backupUploadedFiles($type, $timestamp);
        }

        return Command::SUCCESS;
    }

    protected function ensureDirectoriesExist(): void
    {
        $directories = [
            "{$this->backupPath}/daily",
            "{$this->backupPath}/weekly",
            "{$this->backupPath}/monthly",
            "{$this->backupPath}/manual",
            base_path('../backups/files'),
            base_path('../backups/logs'),
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0700, true);
                $this->info("Created directory: {$dir}");
            }
        }
    }

    protected function backupUploadedFiles(string $type, string $timestamp): void
    {
        $this->info("Backing up uploaded files...");

        $sourceDirs = [
            storage_path('app/financial_files'),
            storage_path('app/public'),
        ];

        $targetDir = base_path("../backups/files/{$type}_{$timestamp}");

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0700, true);
        }

        foreach ($sourceDirs as $sourceDir) {
            if (!is_dir($sourceDir)) {
                continue;
            }

            $dirName = basename($sourceDir);
            $targetPath = "{$targetDir}/{$dirName}";

            // Use cp for copying files
            $cpProcess = new Process(['cp', '-r', $sourceDir, $targetPath]);
            $cpProcess->setTimeout(300);
            $cpProcess->run();

            if ($cpProcess->isSuccessful()) {
                $this->info("  Backed up: {$dirName}");
            } else {
                $this->warn("  Failed to backup: {$dirName}");
            }
        }

        // Create tar.gz archive
        $archivePath = "{$targetDir}.tar.gz";
        $tarProcess = new Process(['tar', '-czf', $archivePath, '-C', $targetDir, '.']);
        $tarProcess->setTimeout(300);
        $tarProcess->run();

        if ($tarProcess->isSuccessful()) {
            // Remove uncompressed directory
            $rmProcess = new Process(['rm', '-rf', $targetDir]);
            $rmProcess->run();
            $this->info("Files backup archived: {$archivePath}");
        }
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
