<?php

namespace App\Services\Document;

use App\Models\Contract;
use App\Models\ContractAnnex;
use App\Models\DocumentFile;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentFileService
{
    /**
     * Get the storage disk name
     */
    protected function getDisk(): string
    {
        return config('filesystems.documents_disk', 'documents');
    }

    /**
     * Get the formatted document name based on type
     *
     * Contracts: CTR SAD XX
     * Annexes: ANX SAD XX to contract XX
     * Offers: OFR SAD XXXX
     */
    protected function getFormattedDocumentName(Model $documentable): string
    {
        if ($documentable instanceof Contract) {
            // CTR SAD 01, CTR SAD 02, etc.
            return 'CTR SAD ' . $documentable->contract_number;
        } elseif ($documentable instanceof ContractAnnex) {
            // ANX SAD 01 to contract 01
            // Extract annex number from annex_code (e.g., '01-A1' -> '01', '01-A05' -> '05')
            $annexCode = $documentable->annex_code;
            preg_match('/A(\d+)/', $annexCode, $matches);
            $annexNum = isset($matches[1]) ? str_pad($matches[1], 2, '0', STR_PAD_LEFT) : str_pad($documentable->id, 2, '0', STR_PAD_LEFT);
            $contractNum = $documentable->contract->contract_number;
            return 'ANX SAD ' . $annexNum . ' to contract ' . $contractNum;
        } elseif ($documentable instanceof Offer) {
            // OFR SAD 0001
            return 'OFR SAD ' . $documentable->offer_number;
        }

        return (string) $documentable->id;
    }

    /**
     * Generate file path with proper naming
     */
    protected function generateFilePath(Model $documentable, string $documentType, int $version): string
    {
        $category = DocumentFile::getCategoryForModel($documentable);
        $year = now()->format('Y');

        // Get base name
        $baseName = $this->getFormattedDocumentName($documentable);

        // Add -signed suffix for signed documents
        if ($documentType === DocumentFile::TYPE_SIGNED) {
            $baseName .= '-signed';
        }

        // Add version suffix if not first version
        if ($version > 1) {
            $baseName .= '-v' . $version;
        }

        return "documents/{$category}/{$year}/{$baseName}.pdf";
    }

    /**
     * Store a draft document (from generated PDF content)
     */
    public function storeDraft(Model $documentable, string $pdfContent, ?string $originalFilename = null): DocumentFile
    {
        return DB::transaction(function () use ($documentable, $pdfContent, $originalFilename) {
            $category = DocumentFile::getCategoryForModel($documentable);
            $version = DocumentFile::getNextVersion($documentable, DocumentFile::TYPE_DRAFT);
            $path = $this->generateFilePath($documentable, DocumentFile::TYPE_DRAFT, $version);

            // Get display name for original_filename
            $displayName = $this->getFormattedDocumentName($documentable) . '.pdf';

            // Deactivate previous active draft
            $this->deactivatePreviousDocuments($documentable, DocumentFile::TYPE_DRAFT);

            // Store the file
            Storage::disk($this->getDisk())->put($path, $pdfContent);

            // Create the record
            $documentFile = DocumentFile::create([
                'uuid' => Str::uuid(),
                'documentable_type' => get_class($documentable),
                'documentable_id' => $documentable->id,
                'category' => $category,
                'document_type' => DocumentFile::TYPE_DRAFT,
                'version' => $version,
                'is_active' => true,
                'file_path' => $path,
                'original_filename' => $originalFilename ?? $displayName,
                'mime_type' => 'application/pdf',
                'file_size' => strlen($pdfContent),
                'file_hash' => hash('sha256', $pdfContent),
                'organization_id' => $this->getOrganizationId($documentable),
                'created_by' => auth()->id(),
            ]);

            // Update the documentable's active file reference
            $this->updateActiveFileReference($documentable, $documentFile, DocumentFile::TYPE_DRAFT);

            Log::info('Draft document stored', [
                'document_file_id' => $documentFile->id,
                'documentable_type' => get_class($documentable),
                'documentable_id' => $documentable->id,
                'version' => $version,
                'path' => $path,
            ]);

            return $documentFile;
        });
    }

    /**
     * Store a signed document (from uploaded file)
     */
    public function storeSignedUpload(Model $documentable, UploadedFile $file): DocumentFile
    {
        return DB::transaction(function () use ($documentable, $file) {
            $category = DocumentFile::getCategoryForModel($documentable);
            $version = DocumentFile::getNextVersion($documentable, DocumentFile::TYPE_SIGNED);
            $path = $this->generateFilePath($documentable, DocumentFile::TYPE_SIGNED, $version);

            // Get display name with -signed suffix
            $displayName = $this->getFormattedDocumentName($documentable) . '-signed.pdf';

            // Deactivate previous active signed document
            $this->deactivatePreviousDocuments($documentable, DocumentFile::TYPE_SIGNED);

            // Get file content for hash
            $content = $file->get();

            // Store the file
            Storage::disk($this->getDisk())->put($path, $content);

            // Create the record
            $documentFile = DocumentFile::create([
                'uuid' => Str::uuid(),
                'documentable_type' => get_class($documentable),
                'documentable_id' => $documentable->id,
                'category' => $category,
                'document_type' => DocumentFile::TYPE_SIGNED,
                'version' => $version,
                'is_active' => true,
                'file_path' => $path,
                'original_filename' => $displayName,
                'mime_type' => $file->getMimeType() ?? 'application/pdf',
                'file_size' => $file->getSize(),
                'file_hash' => hash('sha256', $content),
                'organization_id' => $this->getOrganizationId($documentable),
                'created_by' => auth()->id(),
            ]);

            // Update the documentable's active file reference
            $this->updateActiveFileReference($documentable, $documentFile, DocumentFile::TYPE_SIGNED);

            Log::info('Signed document uploaded', [
                'document_file_id' => $documentFile->id,
                'documentable_type' => get_class($documentable),
                'documentable_id' => $documentable->id,
                'version' => $version,
                'original_filename' => $file->getClientOriginalName(),
            ]);

            return $documentFile;
        });
    }

    /**
     * Get the active draft document for a documentable
     */
    public function getActiveDraft(Model $documentable): ?DocumentFile
    {
        return DocumentFile::forDocumentable($documentable)
            ->drafts()
            ->active()
            ->first();
    }

    /**
     * Get the active signed document for a documentable
     */
    public function getActiveSigned(Model $documentable): ?DocumentFile
    {
        return DocumentFile::forDocumentable($documentable)
            ->signed()
            ->active()
            ->first();
    }

    /**
     * Get all versions of a specific document type for a documentable
     */
    public function getAllVersions(Model $documentable, string $documentType): Collection
    {
        return DocumentFile::forDocumentable($documentable)
            ->where('document_type', $documentType)
            ->orderBy('version', 'desc')
            ->get();
    }

    /**
     * Get all draft versions for a documentable
     */
    public function getDraftVersions(Model $documentable): Collection
    {
        return $this->getAllVersions($documentable, DocumentFile::TYPE_DRAFT);
    }

    /**
     * Get all signed versions for a documentable
     */
    public function getSignedVersions(Model $documentable): Collection
    {
        return $this->getAllVersions($documentable, DocumentFile::TYPE_SIGNED);
    }

    /**
     * Get a specific version
     */
    public function getVersion(Model $documentable, string $documentType, int $version): ?DocumentFile
    {
        return DocumentFile::forDocumentable($documentable)
            ->where('document_type', $documentType)
            ->where('version', $version)
            ->first();
    }

    /**
     * Get all documents for a documentable (both draft and signed)
     */
    public function getAllDocuments(Model $documentable): Collection
    {
        return DocumentFile::forDocumentable($documentable)
            ->orderBy('document_type')
            ->orderBy('version', 'desc')
            ->get();
    }

    /**
     * Download a document file
     */
    public function download(DocumentFile $documentFile): StreamedResponse
    {
        $disk = $this->getDisk();
        
        if (!Storage::disk($disk)->exists($documentFile->file_path)) {
            throw new \RuntimeException('File not found: ' . $documentFile->file_path);
        }

        return Storage::disk($disk)->download(
            $documentFile->file_path,
            $documentFile->display_filename,
            [
                'Content-Type' => $documentFile->mime_type,
            ]
        );
    }

    /**
     * Get a streamed response for viewing inline
     */
    public function view(DocumentFile $documentFile): StreamedResponse
    {
        $disk = $this->getDisk();
        
        if (!Storage::disk($disk)->exists($documentFile->file_path)) {
            throw new \RuntimeException('File not found: ' . $documentFile->file_path);
        }

        return response()->streamDownload(
            function () use ($disk, $documentFile) {
                echo Storage::disk($disk)->get($documentFile->file_path);
            },
            $documentFile->display_filename,
            [
                'Content-Type' => $documentFile->mime_type,
                'Content-Disposition' => 'inline; filename="' . $documentFile->display_filename . '"',
            ]
        );
    }

    /**
     * Deactivate previous active documents of a given type
     */
    protected function deactivatePreviousDocuments(Model $documentable, string $documentType): void
    {
        DocumentFile::forDocumentable($documentable)
            ->where('document_type', $documentType)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    /**
     * Update the documentable's active file reference
     */
    protected function updateActiveFileReference(Model $documentable, DocumentFile $documentFile, string $documentType): void
    {
        $column = $documentType === DocumentFile::TYPE_DRAFT 
            ? 'active_draft_file_id' 
            : 'active_signed_file_id';

        // Only update if the column exists on the model
        if ($documentable instanceof Contract || $documentable instanceof ContractAnnex) {
            $documentable->update([$column => $documentFile->id]);
        }
    }

    /**
     * Get the organization ID from a documentable
     */
    protected function getOrganizationId(Model $documentable): int
    {
        if ($documentable instanceof Contract) {
            return $documentable->organization_id;
        } elseif ($documentable instanceof ContractAnnex) {
            return $documentable->contract->organization_id;
        } elseif ($documentable instanceof Offer) {
            return $documentable->organization_id;
        }

        throw new \InvalidArgumentException('Cannot determine organization_id for: ' . get_class($documentable));
    }

    /**
     * Check if a documentable has any documents
     */
    public function hasDocuments(Model $documentable): bool
    {
        return DocumentFile::forDocumentable($documentable)->exists();
    }

    /**
     * Check if a documentable has an active draft
     */
    public function hasActiveDraft(Model $documentable): bool
    {
        return DocumentFile::forDocumentable($documentable)
            ->drafts()
            ->active()
            ->exists();
    }

    /**
     * Check if a documentable has an active signed document
     */
    public function hasActiveSigned(Model $documentable): bool
    {
        return DocumentFile::forDocumentable($documentable)
            ->signed()
            ->active()
            ->exists();
    }
}
