<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\ContractAnnex;
use App\Models\DocumentFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateDocumentFiles extends Command
{
    protected $signature = 'documents:migrate 
                            {--dry-run : Show what would be migrated without making changes}
                            {--force : Force migration even if document_files already exist}
                            {--delete-old : Delete old files after successful migration}';

    protected $description = 'Migrate existing contract and annex PDF files to the new versioned document storage system';

    protected int $migratedCount = 0;
    protected int $skippedCount = 0;
    protected int $errorCount = 0;

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');
        $deleteOld = $this->option('delete-old');

        $this->info('=== Document Files Migration ===');
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get source disk (where old files are stored)
        $sourceDisk = Storage::disk(config('filesystems.contracts_disk', 'local'));
        
        // Get target disk (new documents disk)
        $targetDisk = Storage::disk(config('filesystems.documents_disk', 'documents'));

        // Migrate contracts
        $this->info('');
        $this->info('Migrating contract PDFs...');
        $this->migrateContracts($sourceDisk, $targetDisk, $isDryRun, $force, $deleteOld);

        // Migrate annexes
        $this->info('');
        $this->info('Migrating annex PDFs...');
        $this->migrateAnnexes($sourceDisk, $targetDisk, $isDryRun, $force, $deleteOld);

        // Summary
        $this->info('');
        $this->info('=== Migration Summary ===');
        $this->info("Migrated: {$this->migratedCount}");
        $this->info("Skipped: {$this->skippedCount}");
        if ($this->errorCount > 0) {
            $this->error("Errors: {$this->errorCount}");
        }

        if ($isDryRun) {
            $this->warn('');
            $this->warn('This was a dry run. Run without --dry-run to apply changes.');
        }

        return $this->errorCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    protected function migrateContracts($sourceDisk, $targetDisk, bool $isDryRun, bool $force, bool $deleteOld): void
    {
        $contracts = Contract::whereNotNull('pdf_path')
            ->where('pdf_path', '!=', '')
            ->get();

        $this->info("Found {$contracts->count()} contracts with PDF files");

        $bar = $this->output->createProgressBar($contracts->count());

        foreach ($contracts as $contract) {
            try {
                $this->migrateDocument(
                    $contract,
                    'contract',
                    $sourceDisk,
                    $targetDisk,
                    $isDryRun,
                    $force,
                    $deleteOld
                );
            } catch (\Exception $e) {
                $this->error("Failed to migrate contract {$contract->id}: " . $e->getMessage());
                Log::error('Document migration failed', [
                    'type' => 'contract',
                    'id' => $contract->id,
                    'error' => $e->getMessage(),
                ]);
                $this->errorCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info('');
    }

    protected function migrateAnnexes($sourceDisk, $targetDisk, bool $isDryRun, bool $force, bool $deleteOld): void
    {
        $annexes = ContractAnnex::whereNotNull('pdf_path')
            ->where('pdf_path', '!=', '')
            ->with('contract')
            ->get();

        $this->info("Found {$annexes->count()} annexes with PDF files");

        $bar = $this->output->createProgressBar($annexes->count());

        foreach ($annexes as $annex) {
            try {
                $this->migrateDocument(
                    $annex,
                    'annex',
                    $sourceDisk,
                    $targetDisk,
                    $isDryRun,
                    $force,
                    $deleteOld
                );
            } catch (\Exception $e) {
                $this->error("Failed to migrate annex {$annex->id}: " . $e->getMessage());
                Log::error('Document migration failed', [
                    'type' => 'annex',
                    'id' => $annex->id,
                    'error' => $e->getMessage(),
                ]);
                $this->errorCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info('');
    }

    protected function migrateDocument(
        $documentable,
        string $category,
        $sourceDisk,
        $targetDisk,
        bool $isDryRun,
        bool $force,
        bool $deleteOld
    ): void {
        $oldPath = $documentable->pdf_path;

        // Check if already has document_files entries
        $existingDraft = DocumentFile::forDocumentable($documentable)
            ->where('document_type', DocumentFile::TYPE_DRAFT)
            ->exists();

        if ($existingDraft && !$force) {
            $this->skippedCount++;
            return;
        }

        // Check if source file exists
        if (!$sourceDisk->exists($oldPath)) {
            $this->line(" <comment>File not found: {$oldPath}</comment>");
            $this->skippedCount++;
            return;
        }

        // Generate new path
        $createdAt = $documentable->created_at ?? now();
        $newPath = DocumentFile::generatePath($category, $createdAt);

        if ($isDryRun) {
            $this->line(" Would migrate: {$oldPath} -> {$newPath}");
            $this->migratedCount++;
            return;
        }

        // Get file contents
        $content = $sourceDisk->get($oldPath);
        
        DB::transaction(function () use (
            $documentable,
            $category,
            $oldPath,
            $newPath,
            $content,
            $sourceDisk,
            $targetDisk,
            $deleteOld
        ) {
            // Store in new location
            $targetDisk->put($newPath, $content);

            // Create document_files record
            $documentFile = DocumentFile::create([
                'uuid' => Str::uuid(),
                'documentable_type' => get_class($documentable),
                'documentable_id' => $documentable->id,
                'category' => $category,
                'document_type' => DocumentFile::TYPE_DRAFT,
                'version' => 1,
                'is_active' => true,
                'file_path' => $newPath,
                'original_filename' => basename($oldPath),
                'mime_type' => 'application/pdf',
                'file_size' => strlen($content),
                'file_hash' => hash('sha256', $content),
                'organization_id' => $this->getOrganizationId($documentable),
                'created_by' => null, // System migration
            ]);

            // Update documentable with new file reference
            $documentable->update([
                'active_draft_file_id' => $documentFile->id,
                'pdf_path' => $newPath, // Update to new path
            ]);

            // Optionally delete old file
            if ($deleteOld && $oldPath !== $newPath) {
                $sourceDisk->delete($oldPath);
            }
        });

        $this->migratedCount++;
    }

    protected function getOrganizationId($documentable): int
    {
        if ($documentable instanceof Contract) {
            return $documentable->organization_id;
        } elseif ($documentable instanceof ContractAnnex) {
            return $documentable->contract->organization_id;
        }

        throw new \InvalidArgumentException('Cannot determine organization_id');
    }
}
