<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Secure File Upload Validation Rule
 * 
 * This rule validates uploaded files using multiple security checks:
 * 1. Real MIME type detection using finfo (not HTTP headers)
 * 2. File extension validation
 * 3. Magic byte signature verification
 * 4. File size limits
 * 
 * Usage:
 * $request->validate([
 *     'file' => ['required', 'file', new SecureFileUpload()],
 * ]);
 */
class SecureFileUpload implements Rule
{
    /**
     * Allowed MIME types mapped to their valid extensions
     */
    protected array $allowedMimes = [
        'application/pdf' => ['pdf'],
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'application/vnd.ms-powerpoint' => ['ppt'],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx'],
        'text/plain' => ['txt'],
        'text/csv' => ['csv'],
        'application/zip' => ['zip'],
        'application/x-rar-compressed' => ['rar'],
    ];

    /**
     * Magic byte signatures for common file types
     * These are the first few bytes that identify file types
     */
    protected array $magicBytes = [
        // PDF files
        '25504446' => ['pdf'],
        // JPEG files
        'ffd8ffe0' => ['jpg', 'jpeg'],
        'ffd8ffe1' => ['jpg', 'jpeg'],
        'ffd8ffe2' => ['jpg', 'jpeg'],
        'ffd8ffe8' => ['jpg', 'jpeg'],
        // PNG files
        '89504e47' => ['png'],
        // GIF files
        '47494638' => ['gif'],
        // MS Office (old format)
        'd0cf11e0' => ['doc', 'xls', 'ppt'],
        // MS Office (new format - ZIP based)
        '504b0304' => ['docx', 'xlsx', 'pptx', 'zip'],
        // RAR files
        '52617221' => ['rar'],
        // Text files (variable, so we skip magic byte check)
        // CSV files (variable, so we skip magic byte check)
    ];

    protected string $errorMessage = '';

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        // Check if file was uploaded successfully
        if (!$value || !$value->isValid()) {
            $this->errorMessage = 'The file upload failed or is invalid.';
            return false;
        }

        // Get file extension
        $extension = strtolower($value->getClientOriginalExtension());
        if (empty($extension)) {
            $this->errorMessage = 'The file must have a valid extension.';
            return false;
        }

        // Step 1: Get REAL MIME type using fileinfo (server-side detection)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMimeType = finfo_file($finfo, $value->getRealPath());
        finfo_close($finfo);

        if ($realMimeType === false) {
            $this->errorMessage = 'Unable to determine file type.';
            \Log::warning('File MIME type detection failed', [
                'filename' => $value->getClientOriginalName(),
            ]);
            return false;
        }

        // Step 2: Check if MIME type is allowed
        if (!isset($this->allowedMimes[$realMimeType])) {
            $this->errorMessage = "File type '{$realMimeType}' is not allowed.";
            \Log::warning('File upload rejected: disallowed MIME type', [
                'filename' => $value->getClientOriginalName(),
                'mime_type' => $realMimeType,
                'extension' => $extension,
                'user_id' => auth()->id(),
            ]);
            return false;
        }

        // Step 3: Verify extension matches MIME type
        if (!in_array($extension, $this->allowedMimes[$realMimeType])) {
            $this->errorMessage = "File extension '{$extension}' does not match the detected file type.";
            \Log::warning('File upload rejected: extension mismatch', [
                'filename' => $value->getClientOriginalName(),
                'mime_type' => $realMimeType,
                'extension' => $extension,
                'expected_extensions' => $this->allowedMimes[$realMimeType],
                'user_id' => auth()->id(),
            ]);
            return false;
        }

        // Step 4: Verify magic bytes (file signature)
        if (!$this->verifyMagicBytes($value, $extension)) {
            $this->errorMessage = 'File signature verification failed. The file may be corrupted or spoofed.';
            return false;
        }

        // Step 5: Additional security checks
        if (!$this->performSecurityChecks($value)) {
            return false;
        }

        return true;
    }

    /**
     * Verify file magic bytes (file signature)
     */
    protected function verifyMagicBytes($file, string $extension): bool
    {
        // Skip magic byte check for text-based formats (txt, csv)
        if (in_array($extension, ['txt', 'csv'])) {
            return true;
        }

        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            \Log::error('Unable to open file for magic byte verification');
            return false;
        }

        // Read first 8 bytes
        $bytes = fread($handle, 8);
        fclose($handle);

        $hex = bin2hex($bytes);

        // Check against known magic bytes
        foreach ($this->magicBytes as $signature => $allowedExts) {
            if (strpos($hex, $signature) === 0) {
                // Found matching signature, verify extension is compatible
                if (in_array($extension, $allowedExts)) {
                    return true;
                }
            }
        }

        \Log::warning('File upload rejected: magic bytes verification failed', [
            'filename' => $file->getClientOriginalName(),
            'extension' => $extension,
            'magic_bytes' => substr($hex, 0, 16),
            'user_id' => auth()->id(),
        ]);

        return false;
    }

    /**
     * Perform additional security checks
     */
    protected function performSecurityChecks($file): bool
    {
        $filename = $file->getClientOriginalName();

        // Check for dangerous filenames
        if (preg_match('/\.(php|phtml|php3|php4|php5|phar|phpt|exe|bat|cmd|sh|bash)$/i', $filename)) {
            $this->errorMessage = 'Files with executable extensions are not allowed.';
            \Log::warning('File upload rejected: dangerous extension in filename', [
                'filename' => $filename,
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
            ]);
            return false;
        }

        // Check for null bytes in filename (path traversal attempt)
        if (strpos($filename, "\0") !== false) {
            $this->errorMessage = 'Invalid filename characters detected.';
            \Log::warning('File upload rejected: null byte in filename', [
                'filename' => $filename,
                'user_id' => auth()->id(),
            ]);
            return false;
        }

        // Check file size (max 10MB by default)
        $maxSize = config('filesystems.max_upload_size', 10240); // KB
        if ($file->getSize() > ($maxSize * 1024)) {
            $this->errorMessage = "File size exceeds maximum allowed ({$maxSize}KB).";
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return $this->errorMessage ?: 'The :attribute must be a valid, safe file.';
    }

    /**
     * Get list of allowed extensions (for documentation)
     */
    public static function getAllowedExtensions(): array
    {
        $rule = new self();
        $extensions = [];
        foreach ($rule->allowedMimes as $extensions_list) {
            $extensions = array_merge($extensions, $extensions_list);
        }
        return array_unique($extensions);
    }
}
