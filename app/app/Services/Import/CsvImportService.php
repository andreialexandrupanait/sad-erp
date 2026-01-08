<?php

namespace App\Services\Import;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

/**
 * CSV Import Service
 *
 * Provides reusable CSV import functionality with validation, error handling,
 * and progress tracking. Eliminates duplicate import logic across controllers.
 */
class CsvImportService
{
    protected int $imported = 0;
    protected int $skipped = 0;
    protected array $errors = [];

    /**
     * Import data from a CSV file
     *
     * @param UploadedFile $file The uploaded CSV file
     * @param array $validationRules Validation rules for each row
     * @param callable $processRow Callback to process each validated row
     * @param callable|null $duplicateCheck Optional callback to check for duplicates
     * @return array Results with imported count, skipped count, and errors
     */
    public function import(
        UploadedFile $file,
        array $validationRules,
        callable $processRow,
        ?callable $duplicateCheck = null
    ): array {
        // Reset counters
        $this->imported = 0;
        $this->skipped = 0;
        $this->errors = [];

        // Parse CSV file
        $csvData = $this->parseCsvFile($file);

        if (empty($csvData)) {
            $this->errors[] = 'CSV file is empty or could not be parsed';
            return $this->getResults();
        }

        $header = array_shift($csvData);
        $header = array_map('trim', $header);

        // Process each row
        foreach ($csvData as $index => $row) {
            $rowNumber = $index + 2; // Account for header + 0-based index

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Combine header with row data
            $data = array_combine($header, $row);

            if ($data === false) {
                $this->errors[] = "Row {$rowNumber}: Column count mismatch";
                $this->skipped++;
                continue;
            }

            // Validate row data
            $validator = Validator::make($data, $validationRules);

            if ($validator->fails()) {
                $this->errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                $this->skipped++;
                continue;
            }

            // Check for duplicates if callback provided
            if ($duplicateCheck && $duplicateCheck($data, $rowNumber)) {
                $this->skipped++;
                continue; // Error message should be added in the callback
            }

            // Process the row
            try {
                $processRow($data, $rowNumber);
                $this->imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $this->skipped++;
            }
        }

        return $this->getResults();
    }

    /**
     * Parse CSV file into array
     *
     * @param UploadedFile $file
     * @return array
     */
    protected function parseCsvFile(UploadedFile $file): array
    {
        try {
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            return $csvData ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Add a duplicate error message
     *
     * @param int $rowNumber
     * @param string $message
     * @return void
     */
    public function addDuplicateError(int $rowNumber, string $message): void
    {
        $this->errors[] = "Row {$rowNumber}: {$message}";
    }

    /**
     * Get import results
     *
     * @return array
     */
    protected function getResults(): array
    {
        return [
            'imported' => $this->imported,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
            'total' => $this->imported + $this->skipped,
        ];
    }

    /**
     * Get a value from data array with fallback keys
     *
     * @param array $data
     * @param string|array $keys Primary key or array of fallback keys
     * @param mixed $default Default value if none found
     * @return mixed
     */
    public function getValue(array $data, string|array $keys, mixed $default = null): mixed
    {
        if (is_string($keys)) {
            $keys = [$keys];
        }

        foreach ($keys as $key) {
            if (isset($data[$key]) && $data[$key] !== '') {
                return trim($data[$key]);
            }
        }

        return $default;
    }

    /**
     * Parse boolean value from CSV data
     *
     * @param array $data
     * @param string|array $keys
     * @param bool $default
     * @return bool
     */
    public function getBooleanValue(array $data, string|array $keys, bool $default = false): bool
    {
        $value = $this->getValue($data, $keys);

        if ($value === null) {
            return $default;
        }

        return in_array(strtolower($value), ['yes', 'da', '1', 'true', 'y']);
    }

    /**
     * Stream CSV template file to browser
     *
     * @param string $filename
     * @param array $headers
     * @param array $exampleRows
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function streamTemplate(string $filename, array $headers, array $exampleRows = []): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->stream(function() use ($headers, $exampleRows) {
            $file = fopen('php://output', 'w');

            // Write headers
            fputcsv($file, $headers);

            // Write example rows
            foreach ($exampleRows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Format success message
     *
     * @param string $entityType
     * @return string
     */
    public function getSuccessMessage(string $entityType): string
    {
        return "{$entityType}: {$this->imported} imported, {$this->skipped} skipped";
    }

    /**
     * Check if import has errors
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get all errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get imported count
     *
     * @return int
     */
    public function getImportedCount(): int
    {
        return $this->imported;
    }

    /**
     * Get skipped count
     *
     * @return int
     */
    public function getSkippedCount(): int
    {
        return $this->skipped;
    }
}
