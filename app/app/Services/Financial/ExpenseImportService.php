<?php

namespace App\Services\Financial;

use App\Models\FinancialExpense;
use App\Models\SettingOption;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Service for handling expense imports from CSV files.
 *
 * Supports category matching with optimized N+1 query prevention.
 */
class ExpenseImportService
{
    /**
     * Pre-loaded categories for fast lookup.
     */
    protected Collection $categories;

    /**
     * Import statistics.
     */
    protected array $stats = [
        'imported' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    /**
     * Organization ID for the current import.
     */
    protected int $organizationId;

    /**
     * User ID for the current import.
     */
    protected int $userId;

    /**
     * Pre-load all expense categories for fast lookup.
     *
     * This eliminates N+1 queries by loading all categories once
     * instead of querying per row.
     */
    public function loadCategoriesIndex(): void
    {
        $this->categories = SettingOption::active()
            ->ordered()
            ->get();
    }

    /**
     * Find category by label (partial match).
     *
     * @param string $label Category label to search for
     * @return SettingOption|null
     */
    public function findCategoryByLabel(string $label): ?SettingOption
    {
        if (empty($label)) {
            return null;
        }

        $label = trim($label);

        // First try exact match
        $category = $this->categories->first(fn($c) => strtolower($c->name) === strtolower($label));

        if ($category) {
            return $category;
        }

        // Then try partial match
        return $this->categories->first(fn($c) => str_contains(strtolower($c->name), strtolower($label)));
    }

    /**
     * Validate an expense row.
     *
     * @param array $data Row data
     * @return array [isValid, errors]
     */
    public function validateRow(array $data): array
    {
        $validator = Validator::make($data, [
            'document_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|in:RON,EUR',
            'occurred_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return [false, $validator->errors()->all()];
        }

        return [true, []];
    }

    /**
     * Process a single expense row.
     *
     * @param array $data Row data
     * @param bool $dryRun Whether to skip actual database operations
     * @return FinancialExpense|null Created expense or null
     */
    public function processRow(array $data, bool $dryRun = false): ?FinancialExpense
    {
        // Find category
        $categoryId = null;
        if (!empty($data['category'] ?? '')) {
            $category = $this->findCategoryByLabel($data['category']);
            $categoryId = $category?->id;
        }

        $occurredAt = Carbon::parse($data['occurred_at']);

        $expenseData = [
            'organization_id' => $this->organizationId,
            'user_id' => $this->userId,
            'document_name' => trim($data['document_name']),
            'amount' => (float) $data['amount'],
            'currency' => strtoupper(trim($data['currency'])),
            'occurred_at' => $occurredAt,
            'year' => $occurredAt->year,
            'month' => $occurredAt->month,
            'category_option_id' => $categoryId,
            'note' => trim($data['note'] ?? ''),
        ];

        if ($dryRun) {
            Log::info('DRY RUN: Would create expense', $expenseData);
            return null;
        }

        return FinancialExpense::create($expenseData);
    }

    /**
     * Import expenses from CSV data.
     *
     * @param array $csvData Parsed CSV rows (first row should be header)
     * @param int $organizationId Organization ID
     * @param int $userId User ID
     * @param bool $dryRun Whether to skip actual database operations
     * @return array Import statistics
     */
    public function import(
        array $csvData,
        int $organizationId,
        int $userId,
        bool $dryRun = false
    ): array {
        $this->organizationId = $organizationId;
        $this->userId = $userId;
        $this->stats = [
            'imported' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        // Get header row
        $header = array_shift($csvData);
        $header = array_map('trim', $header);

        if ($dryRun) {
            Log::info('DRY RUN MODE - No data will be saved');
        }

        // Pre-load categories for fast lookup (fixes N+1)
        $this->loadCategoriesIndex();

        foreach ($csvData as $index => $row) {
            $rowNumber = $index + 2;

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Skip malformed rows
            if (count($row) !== count($header)) {
                $this->stats['errors'][] = "Row {$rowNumber}: Column count mismatch";
                $this->stats['skipped']++;
                continue;
            }

            $data = array_combine($header, $row);

            // Validate row
            [$isValid, $validationErrors] = $this->validateRow($data);
            if (!$isValid) {
                $this->stats['errors'][] = "Row {$rowNumber}: " . implode(', ', $validationErrors);
                $this->stats['skipped']++;
                continue;
            }

            try {
                $this->processRow($data, $dryRun);
                $this->stats['imported']++;
            } catch (\Exception $e) {
                $this->stats['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                $this->stats['skipped']++;
            }
        }

        return $this->stats;
    }

    /**
     * Parse CSV file into array.
     *
     * @param string $filePath Path to CSV file
     * @return array Parsed data rows
     */
    public function parseFile(string $filePath): array
    {
        return array_map('str_getcsv', file($filePath));
    }
}
