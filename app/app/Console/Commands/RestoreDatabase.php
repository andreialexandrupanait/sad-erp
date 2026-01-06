<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RestoreDatabase extends Command
{
    protected $signature = 'backup:restore
                            {file? : Backup file to restore (optional, will show list if not provided)}
                            {--force : Skip confirmation prompt}
                            {--database= : Target database (defaults to current database)}';

    protected $description = 'Restore database from a backup file';

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
        $file = $this->argument('file');
        $force = $this->option('force');
        $targetDatabase = $this->option('database') ?? config('database.connections.mysql.database');

        // If no file specified, show available backups
        if (!$file) {
            $file = $this->selectBackupFile();
            if (!$file) {
                return Command::SUCCESS;
            }
        }

        // Find the file
        $filepath = $this->findBackupFile($file);
        if (!$filepath) {
            $this->error("Backup file not found: {$file}");
            return Command::FAILURE;
        }

        $filesize = filesize($filepath);
        $this->info("Backup file: {$filepath}");
        $this->info("File size: " . $this->formatBytes($filesize));
        $this->info("Target database: {$targetDatabase}");
        $this->newLine();

        // Confirm restore
        if (!$force) {
            $this->warn("WARNING: This will REPLACE ALL DATA in the '{$targetDatabase}' database!");
            if (!$this->confirm('Are you sure you want to proceed?', false)) {
                $this->info("Restore cancelled.");
                return Command::SUCCESS;
            }
        }

        // Get database credentials
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        // Build restore command
        $isCompressed = str_ends_with($filepath, '.gz');

        if ($isCompressed) {
            $command = sprintf(
                'gunzip -c %s | mysql --host=%s --port=%s --user=%s --password=%s %s',
                escapeshellarg($filepath),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($targetDatabase)
            );
        } else {
            $command = sprintf(
                'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($targetDatabase),
                escapeshellarg($filepath)
            );
        }

        $this->info("Restoring database...");
        $startTime = microtime(true);

        // Use Symfony Process for better security and error handling
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(1800); // 30 minutes max for large database restores

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $error = $process->getErrorOutput() ?: $process->getOutput();
            $this->error("Restore failed: {$error}");
            Log::error("Database restore failed", [
                'file' => $filepath,
                'database' => $targetDatabase,
                'error' => $error,
                'return_code' => $process->getExitCode(),
            ]);
            return Command::FAILURE;
        }

        $duration = round(microtime(true) - $startTime, 2);

        $this->info("Database restored successfully!");
        $this->info("Duration: {$duration}s");

        Log::info("Database restored", [
            'file' => $filepath,
            'database' => $targetDatabase,
            'duration' => $duration,
        ]);

        return Command::SUCCESS;
    }

    protected function selectBackupFile(): ?string
    {
        $this->info("Available backups:");
        $this->newLine();

        $allFiles = [];

        foreach (['daily', 'weekly', 'monthly', 'manual'] as $type) {
            $dir = "{$this->backupPath}/{$type}";
            if (!is_dir($dir)) {
                continue;
            }

            $files = glob("{$dir}/backup_*.sql*");
            usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

            if (empty($files)) {
                continue;
            }

            $this->info(strtoupper($type) . " backups:");
            foreach (array_slice($files, 0, 5) as $file) { // Show last 5 per type
                $filename = basename($file);
                $filesize = $this->formatBytes(filesize($file));
                $date = date('Y-m-d H:i:s', filemtime($file));
                $allFiles[] = $file;
                $index = count($allFiles);
                $this->line("  [{$index}] {$filename} ({$filesize}) - {$date}");
            }
            $this->newLine();
        }

        if (empty($allFiles)) {
            $this->warn("No backup files found.");
            return null;
        }

        $choice = $this->ask("Enter backup number to restore (or 'q' to quit)");

        if ($choice === 'q' || $choice === null) {
            return null;
        }

        $index = (int)$choice - 1;
        if (!isset($allFiles[$index])) {
            $this->error("Invalid selection.");
            return null;
        }

        return $allFiles[$index];
    }

    protected function findBackupFile(string $file): ?string
    {
        // If it's an absolute path
        if (file_exists($file)) {
            return $file;
        }

        // Search in backup directories
        foreach (['daily', 'weekly', 'monthly', 'manual'] as $type) {
            $path = "{$this->backupPath}/{$type}/{$file}";
            if (file_exists($path)) {
                return $path;
            }
        }

        // Try with .gz extension
        foreach (['daily', 'weekly', 'monthly', 'manual'] as $type) {
            $path = "{$this->backupPath}/{$type}/{$file}.gz";
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
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
