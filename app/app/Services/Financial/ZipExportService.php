<?php

namespace App\Services\Financial;

use App\Models\FinancialFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use ZipArchive;

/**
 * Financial ZIP Export Service
 *
 * Handles creation of ZIP archives for financial files with proper organization
 */
class ZipExportService
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Create a ZIP archive for files from a specific month
     *
     * @param int $year
     * @param int $month
     * @return array ['success' => bool, 'path' => string|null, 'filename' => string|null, 'error' => string|null]
     */
    public function createMonthlyZip(int $year, int $month): array
    {
        // Get all files for the specified month
        $files = FinancialFile::where('an', $year)
            ->where('luna', $month)
            ->get();

        if ($files->isEmpty()) {
            return [
                'success' => false,
                'path' => null,
                'filename' => null,
                'error' => 'No files found for this month',
            ];
        }

        // Generate ZIP filename
        $monthName = \Carbon\Carbon::create()->setMonth($month)->locale('en')->format('F');
        $monthPadded = str_pad($month, 2, '0', STR_PAD_LEFT);
        $zipFileName = "{$monthPadded} - {$monthName}.zip";

        return $this->createZipFromFiles($files, $zipFileName);
    }

    /**
     * Create a ZIP archive for files from an entire year
     *
     * @param int $year
     * @return array ['success' => bool, 'path' => string|null, 'filename' => string|null, 'error' => string|null]
     */
    public function createYearlyZip(int $year): array
    {
        // Get all files for the specified year
        $files = FinancialFile::where('an', $year)->get();

        if ($files->isEmpty()) {
            return [
                'success' => false,
                'path' => null,
                'filename' => null,
                'error' => 'No files found for this year',
            ];
        }

        // Generate ZIP filename
        $zipFileName = "{$year}.zip";

        return $this->createZipFromFiles($files, $zipFileName, true);
    }

    /**
     * Create ZIP archive from a collection of files
     *
     * @param Collection $files
     * @param string $zipFileName
     * @param bool $includeMonthFolders Whether to organize by month (for yearly ZIPs)
     * @return array
     */
    protected function createZipFromFiles(Collection $files, string $zipFileName, bool $includeMonthFolders = false): array
    {
        // Create temporary ZIP file path
        $tempZipPath = $this->getTempZipPath($zipFileName);

        // Ensure temp directory exists
        $this->ensureTempDirectoryExists();

        // Create ZIP archive
        $zip = new ZipArchive();
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return [
                'success' => false,
                'path' => null,
                'filename' => null,
                'error' => 'Failed to create ZIP archive',
            ];
        }

        // Add files to ZIP
        $addedCount = 0;
        foreach ($files as $file) {
            if ($this->addFileToZip($zip, $file, $includeMonthFolders)) {
                $addedCount++;
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            // Clean up empty ZIP
            @unlink($tempZipPath);
            return [
                'success' => false,
                'path' => null,
                'filename' => null,
                'error' => 'No valid files could be added to ZIP',
            ];
        }

        return [
            'success' => true,
            'path' => $tempZipPath,
            'filename' => $zipFileName,
            'error' => null,
        ];
    }

    /**
     * Add a single file to the ZIP archive
     *
     * @param ZipArchive $zip
     * @param FinancialFile $file
     * @param bool $includeMonthFolders
     * @return bool
     */
    protected function addFileToZip(ZipArchive $zip, FinancialFile $file, bool $includeMonthFolders): bool
    {
        if (!Storage::disk('financial')->exists($file->file_path)) {
            return false;
        }

        $tip = $file->tip ?? 'general';
        $folderName = $this->fileUploadService->mapTipToFolderName($tip);

        // Build internal path in ZIP
        $internalPath = $folderName . '/' . $file->file_name;

        if ($includeMonthFolders) {
            // For yearly ZIPs, organize by month
            $monthName = \Carbon\Carbon::create()->setMonth($file->luna)->locale('en')->format('F');
            $monthPadded = str_pad($file->luna, 2, '0', STR_PAD_LEFT);
            $internalPath = "{$monthPadded} - {$monthName}/{$folderName}/{$file->file_name}";
        }

        // Get file contents and add to ZIP
        $fileContents = Storage::disk('financial')->get($file->file_path);
        return $zip->addFromString($internalPath, $fileContents);
    }

    /**
     * Get temporary ZIP file path
     *
     * @param string $filename
     * @return string
     */
    protected function getTempZipPath(string $filename): string
    {
        return storage_path("app/temp/{$filename}");
    }

    /**
     * Ensure temp directory exists
     *
     * @return void
     */
    protected function ensureTempDirectoryExists(): void
    {
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
    }

    /**
     * Create download response for ZIP file
     *
     * @param string $tempZipPath
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function createDownloadResponse(string $tempZipPath, string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return response()->download($tempZipPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Clean up old temporary ZIP files (older than 1 hour)
     *
     * @return int Number of files deleted
     */
    public function cleanupOldTempFiles(): int
    {
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            return 0;
        }

        $deleted = 0;
        $files = glob($tempDir . '/*.zip');
        $oneHourAgo = time() - 3600;

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $oneHourAgo) {
                if (@unlink($file)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }
}
