<?php

namespace App\Services\Financial;

use App\Models\FinancialFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Financial File Upload Service
 *
 * Handles file uploads for financial documents with proper naming,
 * storage organization, and metadata management.
 */
class FileUploadService
{
    /**
     * Upload multiple files with proper naming and organization
     *
     * @param array $files Array of UploadedFile instances
     * @param int $year Year for file organization
     * @param int $month Month for file organization
     * @param string $tip File type (incasare, plata, extrase, etc.)
     * @param array $metadata Additional metadata (entity_type, entity_id, etc.)
     * @return array Array of created FinancialFile models
     */
    public function uploadFiles(
        array $files,
        int $year,
        int $month,
        string $tip,
        array $metadata = []
    ): array {
        $uploadedFiles = [];

        // Get Romanian month name for folder structure
        $monthName = romanian_month($month);

        foreach ($files as $file) {
            $uploadedFile = $this->uploadSingleFile($file, $year, $month, $monthName, $tip, $metadata);
            if ($uploadedFile) {
                $uploadedFiles[] = $uploadedFile;
            }
        }

        return $uploadedFiles;
    }

    /**
     * Upload a single file
     *
     * @param UploadedFile $file
     * @param int $year
     * @param int $month
     * @param string $monthName
     * @param string $tip
     * @param array $metadata
     * @return FinancialFile|null
     */
    protected function uploadSingleFile(
        UploadedFile $file,
        int $year,
        int $month,
        string $monthName,
        string $tip,
        array $metadata
    ): ?FinancialFile {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();

        // Generate file name (with special handling for bank statements)
        [$displayName, $newFileName] = $this->generateFileName($originalName, $extension, $tip);

        // Get storage path
        $storagePath = $this->getStoragePath($year, $monthName, $tip, $newFileName);

        // Store file
        $path = $file->storeAs(dirname($storagePath), basename($storagePath), 'financial');

        if (!$path) {
            return null;
        }

        // Create database record
        return FinancialFile::create([
            'file_name' => $displayName,
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'entity_type' => $metadata['entity_type'] ?? null,
            'entity_id' => $metadata['entity_id'] ?? null,
            'an' => $year,
            'luna' => $month,
            'tip' => $tip,
        ]);
    }

    /**
     * Generate appropriate file name based on type
     *
     * @param string $originalName
     * @param string $extension
     * @param string $tip
     * @return array [displayName, serverFileName]
     */
    protected function generateFileName(string $originalName, string $extension, string $tip): array
    {
        $displayName = $originalName;
        $newFileName = null;

        // Auto-rename bank statements for better readability
        if ($tip === 'extrase') {
            $generatedName = $this->generateBankStatementName($originalName);
            if ($generatedName) {
                $displayName = $generatedName . '.' . $extension;
                $newFileName = $displayName;
            }
        }

        // If not a bank statement or rename failed, use standard naming
        if (!$newFileName) {
            $sanitizedName = sanitize_filename(pathinfo($originalName, PATHINFO_FILENAME));
            $uniqueId = Str::uuid()->toString();
            $newFileName = "{$sanitizedName}-{$uniqueId}.{$extension}";
        }

        return [$displayName, $newFileName];
    }

    /**
     * Generate friendly name for bank statement files
     *
     * @param string $filename Original filename
     * @return string|null Friendly name or null if not a recognized format
     */
    protected function generateBankStatementName(string $filename): ?string
    {
        // Pattern: IBAN_YYYY-MM-DD_YYYY-MM-DD.pdf or variations
        // Extract IBAN and dates if possible

        // Remove extension for parsing
        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

        // Try to extract IBAN (usually starts with RO and is 24 characters)
        if (preg_match('/RO\d{2}[A-Z]{4}\d{16}/', $nameWithoutExt, $ibanMatch)) {
            $iban = $ibanMatch[0];

            // Try to extract dates
            if (preg_match_all('/(\d{4})-(\d{2})-(\d{2})/', $nameWithoutExt, $dateMatches)) {
                if (count($dateMatches[0]) >= 2) {
                    $startDate = $dateMatches[0][0];
                    $endDate = $dateMatches[0][1];

                    // Determine currency from IBAN or filename
                    $currency = 'RON'; // Default
                    if (str_contains(strtoupper($nameWithoutExt), 'EUR')) {
                        $currency = 'EUR';
                    } elseif (str_contains(strtoupper($nameWithoutExt), 'USD')) {
                        $currency = 'USD';
                    }

                    // Format: "Extras BT RON 01.12.2024 - 31.12.2024"
                    $startFormatted = date('d.m.Y', strtotime($startDate));
                    $endFormatted = date('d.m.Y', strtotime($endDate));

                    return "Extras BT {$currency} {$startFormatted} - {$endFormatted}";
                }
            }

            // If we have IBAN but couldn't parse dates, use simpler format
            return "Extras BT " . substr($iban, -8);
        }

        // If we can't parse it, return null to use standard naming
        return null;
    }

    /**
     * Get storage path for file
     *
     * @param int $year
     * @param string $monthName
     * @param string $tip
     * @param string $fileName
     * @return string
     */
    protected function getStoragePath(int $year, string $monthName, string $tip, string $fileName): string
    {
        $folderName = $this->mapTipToFolderName($tip);
        return "{$year}/{$monthName}/{$folderName}/{$fileName}";
    }

    /**
     * Map database tip values to Romanian folder names
     *
     * @param string $tip
     * @return string
     */
    public function mapTipToFolderName(string $tip): string
    {
        return match($tip) {
            'incasare' => 'Incasari',
            'plata' => 'Plati',
            'extrase' => 'Extrase',
            default => 'General',
        };
    }

    /**
     * Delete a file from storage
     *
     * @param FinancialFile $file
     * @return bool
     */
    public function deleteFile(FinancialFile $file): bool
    {
        if ($file->file_path && Storage::disk('financial')->exists($file->file_path)) {
            return Storage::disk('financial')->delete($file->file_path);
        }

        return false;
    }

    /**
     * Get file download response
     *
     * @param FinancialFile $file
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|null
     */
    public function downloadFile(FinancialFile $file): ?\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        if (!Storage::disk('financial')->exists($file->file_path)) {
            return null;
        }

        $fullPath = Storage::disk('financial')->path($file->file_path);
        return response()->download($fullPath, $file->file_name);
    }
}
