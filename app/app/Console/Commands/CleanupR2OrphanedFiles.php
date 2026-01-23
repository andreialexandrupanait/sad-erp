<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\ContractAnnex;
use App\Models\DocumentFile;
use App\Models\FinancialFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupR2OrphanedFiles extends Command
{
    /**
     * Protected prefixes that are NEVER deleted by default.
     * These contain critical business data that should not be auto-cleaned.
     */
    protected const PROTECTED_PREFIXES = [
        'financial/',
        'documents/contract/',
        'documents/annex/',
        'documents/offer/',
        'contracts/',
        'annexes/',
        // Legacy paths
        'documents/contract/legacy/',
        'documents/annex/legacy/',
    ];

    /**
     * Known safe-to-clean prefixes (temporary/generated files only).
     */
    protected const SAFE_SCOPES = [
        'temp' => ['temp/', 'tmp/', 'cache/'],
        'generated' => ['generated/', 'previews/'],
    ];

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'storage:cleanup-r2
                            {--dry-run : Preview without any changes (default safe mode)}
                            {--force : Required to perform actual deletions}
                            {--scope= : Only process specific scope (temp|generated)}
                            {--include-prefix=* : Only process files matching these prefixes}
                            {--exclude-prefix=* : Additional prefixes to exclude (on top of protected)}
                            {--only-protect=* : Override default protected prefixes (use with caution!)}
                            {--trash-prefix= : Move files to trash instead of deleting (e.g., trash/240123/)}
                            {--show-protected : Also list protected files in the report}
                            {--skip-db-check : Skip database reference check (only use prefix rules)}';

    /**
     * The console command description.
     */
    protected $description = 'Analyze and safely clean up R2 storage. Safe-by-default: financial and contract files are ALWAYS protected.';

    protected array $excludePrefixes = [];
    protected array $includePrefixes = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run') || !$this->option('force');
        $scope = $this->option('scope');
        $trashPrefix = $this->option('trash-prefix');
        $showProtected = $this->option('show-protected');
        $skipDbCheck = $this->option('skip-db-check');

        // Build exclude list (protected + user-specified)
        $onlyProtect = $this->option('only-protect');
        if (!empty($onlyProtect)) {
            // Override default protected prefixes
            $this->excludePrefixes = array_merge($onlyProtect, $this->option('exclude-prefix') ?: []);
            $this->warn('⚠️  Using custom protected prefixes: ' . implode(', ', $onlyProtect));
        } else {
            $this->excludePrefixes = array_merge(
                self::PROTECTED_PREFIXES,
                $this->option('exclude-prefix') ?: []
            );
        }

        // Build include list
        $this->includePrefixes = $this->option('include-prefix') ?: [];

        // If scope is specified, use those prefixes
        if ($scope && isset(self::SAFE_SCOPES[$scope])) {
            $this->includePrefixes = self::SAFE_SCOPES[$scope];
        }

        $this->displayHeader($dryRun, $scope, $trashPrefix);

        // Get all files from R2
        $r2Disk = Storage::disk('r2');
        $this->info('Scanning R2 storage...');
        $allR2Files = $this->getAllR2Files($r2Disk);
        $this->info("Found " . count($allR2Files) . " total files in R2.");
        $this->newLine();

        // Collect database references (unless skipped)
        $referencedPaths = collect();
        if (!$skipDbCheck) {
            $this->info('Checking database references...');
            $referencedPaths = $this->collectReferencedPaths();
            $this->info("Found {$referencedPaths->count()} file references in database.");
            $this->newLine();
        }

        // Categorize files
        $categories = $this->categorizeFiles($allR2Files, $referencedPaths);

        // Display summary per prefix
        $this->displayPrefixSummary($categories, $r2Disk);

        // Display detailed report
        $this->displayDetailedReport($categories, $r2Disk, $showProtected);

        // Handle deletable files
        $deletableFiles = $categories['deletable'];

        if ($deletableFiles->isEmpty()) {
            $this->info('No files eligible for cleanup based on current rules.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $this->warn("Files eligible for cleanup: {$deletableFiles->count()}");

        if ($dryRun) {
            $this->newLine();
            $this->comment('DRY RUN MODE - No changes made.');
            $this->comment('To perform cleanup, use: --force --scope=<scope> or --force --include-prefix=<prefix>');
            $this->comment('To move instead of delete, add: --trash-prefix=trash/' . date('Ymd') . '/');
            return Command::SUCCESS;
        }

        // Safety check: require explicit scope, include-prefix, or only-protect for force mode
        if (empty($this->includePrefixes) && !$scope && empty($onlyProtect)) {
            $this->error('SAFETY: --force requires one of:');
            $this->error('  --scope=<scope>');
            $this->error('  --include-prefix=<prefix>');
            $this->error('  --only-protect=<prefix> (to override default protections)');
            $this->error('This prevents accidental deletion of important files.');
            return Command::FAILURE;
        }

        // Confirm action
        if (!$this->confirm('Proceed with cleanup? This action may be irreversible.', false)) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        // Perform cleanup
        if ($trashPrefix) {
            $this->moveToTrash($deletableFiles, $r2Disk, $trashPrefix);
        } else {
            $this->deleteFiles($deletableFiles, $r2Disk);
        }

        return Command::SUCCESS;
    }

    /**
     * Display command header with current settings.
     */
    protected function displayHeader(bool $dryRun, ?string $scope, ?string $trashPrefix): void
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║           R2 Storage Cleanup - Safe Mode                     ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line('Settings:');
        $this->line('  Mode: ' . ($dryRun ? '<fg=green>DRY RUN (safe)</>' : '<fg=red>LIVE</>'));
        if ($scope) {
            $this->line("  Scope: <fg=yellow>{$scope}</>");
        }
        if ($trashPrefix) {
            $this->line("  Trash prefix: <fg=cyan>{$trashPrefix}</>");
        }
        $this->line('  Protected prefixes: <fg=green>' . implode(', ', $this->excludePrefixes) . '</>');
        if (!empty($this->option('exclude-prefix'))) {
            $this->line('  Extra exclusions: ' . implode(', ', $this->option('exclude-prefix')));
        }
        if (!empty($this->includePrefixes)) {
            $this->line('  Include only: <fg=yellow>' . implode(', ', $this->includePrefixes) . '</>');
        }
        $this->newLine();
    }

    /**
     * Categorize files into protected, referenced, and deletable.
     */
    protected function categorizeFiles(array $allFiles, \Illuminate\Support\Collection $referencedPaths): array
    {
        $protected = collect();
        $referenced = collect();
        $deletable = collect();
        $notInScope = collect();

        foreach ($allFiles as $path) {
            // Check if file is protected by prefix
            if ($this->isProtectedPath($path)) {
                $protected->push($path);
                continue;
            }

            // Check if file matches include prefixes (if specified)
            if (!empty($this->includePrefixes) && !$this->matchesIncludePrefixes($path)) {
                $notInScope->push($path);
                continue;
            }

            // Check if file is referenced in database
            if ($this->isReferencedInDb($path, $referencedPaths)) {
                $referenced->push($path);
                continue;
            }

            // File is eligible for deletion
            $deletable->push($path);
        }

        return [
            'protected' => $protected,
            'referenced' => $referenced,
            'deletable' => $deletable,
            'not_in_scope' => $notInScope,
        ];
    }

    /**
     * Check if a path is protected.
     */
    protected function isProtectedPath(string $path): bool
    {
        foreach ($this->excludePrefixes as $prefix) {
            if (str_starts_with($path, $prefix) || str_starts_with($path, ltrim($prefix, '/'))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if path matches include prefixes.
     */
    protected function matchesIncludePrefixes(string $path): bool
    {
        foreach ($this->includePrefixes as $prefix) {
            if (str_starts_with($path, $prefix) || str_starts_with($path, ltrim($prefix, '/'))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if file is referenced in database.
     */
    protected function isReferencedInDb(string $path, \Illuminate\Support\Collection $referencedPaths): bool
    {
        $pathsToCheck = [
            $path,
            ltrim($path, '/'),
            '/' . ltrim($path, '/'),
        ];

        foreach ($pathsToCheck as $checkPath) {
            if ($referencedPaths->contains($checkPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Display summary grouped by prefix.
     */
    protected function displayPrefixSummary(array $categories, $disk): void
    {
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                    SUMMARY BY PREFIX                         ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $allFiles = collect()
            ->merge($categories['protected'])
            ->merge($categories['referenced'])
            ->merge($categories['deletable'])
            ->merge($categories['not_in_scope']);

        // Group by top-level prefix
        $byPrefix = $allFiles->groupBy(function ($path) {
            $parts = explode('/', $path);
            return $parts[0] ?? 'root';
        });

        $headers = ['Prefix', 'Total Files', 'Protected', 'Referenced', 'Deletable', 'Total Size'];
        $rows = [];

        foreach ($byPrefix as $prefix => $files) {
            $protectedCount = $files->filter(fn($f) => $categories['protected']->contains($f))->count();
            $referencedCount = $files->filter(fn($f) => $categories['referenced']->contains($f))->count();
            $deletableCount = $files->filter(fn($f) => $categories['deletable']->contains($f))->count();

            $totalSize = $files->sum(function ($path) use ($disk) {
                try {
                    return $disk->size($path);
                } catch (\Exception $e) {
                    return 0;
                }
            });

            $rows[] = [
                $prefix . '/',
                $files->count(),
                $protectedCount > 0 ? "<fg=green>{$protectedCount}</>" : '0',
                $referencedCount,
                $deletableCount > 0 ? "<fg=yellow>{$deletableCount}</>" : '0',
                $this->formatBytes($totalSize),
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();

        // Overall summary
        $this->line('Overall:');
        $this->line("  <fg=green>Protected (never deleted):</> {$categories['protected']->count()} files");
        $this->line("  <fg=blue>Referenced in database:</> {$categories['referenced']->count()} files");
        $this->line("  <fg=gray>Not in scope:</> {$categories['not_in_scope']->count()} files");
        $this->line("  <fg=yellow>Eligible for cleanup:</> {$categories['deletable']->count()} files");
        $this->newLine();
    }

    /**
     * Display detailed report of deletable files.
     */
    protected function displayDetailedReport(array $categories, $disk, bool $showProtected): void
    {
        if ($showProtected && $categories['protected']->isNotEmpty()) {
            $this->info('Protected files (will NEVER be deleted):');
            $categories['protected']->take(20)->each(fn($p) => $this->line("  <fg=green>✓</> {$p}"));
            if ($categories['protected']->count() > 20) {
                $this->line("  ... and " . ($categories['protected']->count() - 20) . " more");
            }
            $this->newLine();
        }

        if ($categories['deletable']->isNotEmpty()) {
            $this->warn('Files eligible for cleanup:');
            $totalSize = 0;
            $categories['deletable']->take(30)->each(function ($path) use ($disk, &$totalSize) {
                try {
                    $size = $disk->size($path);
                    $totalSize += $size;
                    $this->line("  <fg=yellow>○</> {$path} ({$this->formatBytes($size)})");
                } catch (\Exception $e) {
                    $this->line("  <fg=yellow>○</> {$path} (size unknown)");
                }
            });
            if ($categories['deletable']->count() > 30) {
                $this->line("  ... and " . ($categories['deletable']->count() - 30) . " more files");
            }

            $fullTotalSize = $categories['deletable']->sum(function ($path) use ($disk) {
                try {
                    return $disk->size($path);
                } catch (\Exception $e) {
                    return 0;
                }
            });
            $this->newLine();
            $this->line("Total size eligible for cleanup: <fg=yellow>{$this->formatBytes($fullTotalSize)}</>");
        }
    }

    /**
     * Move files to trash prefix instead of deleting.
     */
    protected function moveToTrash(\Illuminate\Support\Collection $files, $disk, string $trashPrefix): void
    {
        $trashPrefix = rtrim($trashPrefix, '/') . '/';
        $moved = 0;
        $failed = 0;
        $log = [];

        $this->info("Moving {$files->count()} files to {$trashPrefix}...");
        $this->newLine();

        $this->withProgressBar($files, function ($path) use ($disk, $trashPrefix, &$moved, &$failed, &$log) {
            $trashPath = $trashPrefix . $path;
            try {
                // Copy to trash location
                $content = $disk->get($path);
                $disk->put($trashPath, $content);

                // Delete original
                $disk->delete($path);

                $log[] = ['action' => 'moved', 'from' => $path, 'to' => $trashPath];
                $moved++;
            } catch (\Exception $e) {
                $log[] = ['action' => 'failed', 'path' => $path, 'error' => $e->getMessage()];
                $failed++;
            }
        });

        $this->newLine(2);
        $this->info("Cleanup complete!");
        $this->info("  Moved to trash: {$moved} files");
        if ($failed > 0) {
            $this->warn("  Failed: {$failed} files");
        }
        $this->line("  Trash location: {$trashPrefix}");

        // Log the operation
        $logPath = "cleanup-logs/cleanup-" . date('Y-m-d-His') . ".json";
        try {
            $disk->put($logPath, json_encode($log, JSON_PRETTY_PRINT));
            $this->line("  Log saved to: {$logPath}");
        } catch (\Exception $e) {
            $this->warn("  Could not save log: " . $e->getMessage());
        }
    }

    /**
     * Delete files permanently.
     */
    protected function deleteFiles(\Illuminate\Support\Collection $files, $disk): void
    {
        $deleted = 0;
        $failed = 0;

        $this->warn("Permanently deleting {$files->count()} files...");
        $this->newLine();

        $this->withProgressBar($files, function ($path) use ($disk, &$deleted, &$failed) {
            try {
                $disk->delete($path);
                $deleted++;
            } catch (\Exception $e) {
                $failed++;
            }
        });

        $this->newLine(2);
        $this->info("Cleanup complete!");
        $this->info("  Deleted: {$deleted} files");
        if ($failed > 0) {
            $this->warn("  Failed: {$failed} files");
        }
    }

    /**
     * Collect all file paths referenced in the database.
     */
    protected function collectReferencedPaths(): \Illuminate\Support\Collection
    {
        $paths = collect();

        // Financial files
        $paths = $paths->merge(
            FinancialFile::pluck('file_path')->filter()
        );

        // Document files (new system - handles contracts, annexes, and offers)
        $paths = $paths->merge(
            DocumentFile::pluck('file_path')->filter()
        );

        // Contract PDF paths (legacy field)
        $paths = $paths->merge(
            Contract::whereNotNull('pdf_path')->pluck('pdf_path')
        );

        // Contract Annex PDF paths (legacy field)
        $paths = $paths->merge(
            ContractAnnex::whereNotNull('pdf_path')->pluck('pdf_path')
        );

        return $paths->unique()->values();
    }

    /**
     * Get all files from R2 storage recursively.
     */
    protected function getAllR2Files($disk, string $directory = ''): array
    {
        try {
            return $disk->allFiles($directory);
        } catch (\Exception $e) {
            $this->error("Error scanning R2: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Format bytes to human readable string.
     */
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
