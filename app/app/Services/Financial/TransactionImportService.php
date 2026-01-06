<?php

namespace App\Services\Financial;

use App\Models\FinancialExpense;
use App\Models\FinancialFile;
use App\Models\FinancialRevenue;
use App\Models\SettingOption;
use App\Services\Banking\BankStatementPdfParser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Transaction Import Service - Business logic for importing bank statement transactions.
 *
 * Handles:
 * - Parsing bank statement PDFs
 * - Duplicate detection
 * - Creating expenses and revenues from transactions
 */
class TransactionImportService
{
    /**
     * Parse a bank statement file and return transactions with duplicate detection.
     *
     * @param FinancialFile $file The bank statement file
     * @return array Contains 'success', 'metadata', 'transactions', 'categories', and optionally 'error'
     */
    public function parseAndPrepareTransactions(FinancialFile $file): array
    {
        // Verify it's a PDF bank statement
        if ($file->tip !== 'extrase') {
            return [
                'success' => false,
                'error' => __('Acest fisier nu este un extras de cont.'),
            ];
        }

        if (!in_array($file->file_type, ['application/pdf', 'application/x-pdf'])) {
            return [
                'success' => false,
                'error' => __('Doar fisierele PDF pot fi procesate pentru import.'),
            ];
        }

        // Get the file path
        $filePath = Storage::disk('financial')->path($file->file_path);

        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'error' => __('Fisierul nu a fost gasit pe server.'),
            ];
        }

        // Parse the PDF - only get debit transactions (payments/expenses)
        $parser = new BankStatementPdfParser();
        $result = $parser->getDebitTransactions($filePath);

        if (!empty($result['errors'])) {
            return [
                'success' => false,
                'error' => __('Eroare la parsarea PDF: ') . implode(', ', $result['errors']),
            ];
        }

        // Get expense categories with hierarchy
        $categories = $this->getExpenseCategories();

        // Build maps for auto-categorization
        $allCategories = $categories->flatMap(function ($cat) {
            return collect([$cat])->merge($cat->children ?? []);
        });
        $categoryValueToId = $allCategories->pluck('id', 'value')->toArray();
        // Also build label => ID map for direct matching
        $categoryLabelToId = $allCategories->mapWithKeys(function ($cat) {
            return [strtolower($cat->label) => $cat->id];
        })->toArray();

        // Check for existing transactions to detect duplicates
        $existingTransactions = $this->getExistingTransactions(
            $file->an,
            $file->luna,
            $result['metadata']['currency'] ?? 'RON'
        );

        // Mark duplicates in transactions and set suggested category
        foreach ($result['transactions'] as &$transaction) {
            // Try to find category: first from mapping, then from direct label/value match
            $suggestedCategoryId = null;

            if (!empty($transaction['suggested_category'])) {
                // Convert mapping value to ID
                $suggestedCategoryId = $categoryValueToId[$transaction['suggested_category']] ?? null;
            }

            // If no mapping found, try direct match against category labels/values
            if (!$suggestedCategoryId && !empty($transaction['description'])) {
                // Helper to check if pattern matches as whole word or word prefix
                $matchesWord = function($pattern, $text) {
                    // Match whole word OR word that starts with pattern (e.g., "postmark" matches "POSTMARKAPP")
                    return preg_match('/\b' . preg_quote($pattern, '/') . '(\b|\w)/i', $text);
                };

                // Check category labels (min 4 chars to avoid false positives)
                foreach ($categoryLabelToId as $label => $catId) {
                    if (strlen($label) >= 4 && $matchesWord($label, $transaction['description'])) {
                        $suggestedCategoryId = $catId;
                        break;
                    }
                }

                // Also check category values
                if (!$suggestedCategoryId) {
                    foreach ($categoryValueToId as $value => $catId) {
                        if (strlen($value) >= 4 && $matchesWord($value, $transaction['description'])) {
                            $suggestedCategoryId = $catId;
                            break;
                        }
                    }
                }
            }

            $transaction['suggested_category'] = $suggestedCategoryId;

            $duplicateInfo = $this->findDuplicateInfo($transaction, $existingTransactions);
            if ($duplicateInfo) {
                $transaction['is_duplicate'] = true;
                $transaction['existing_id'] = $duplicateInfo['existing_id'];
                $transaction['existing_entity_type'] = $duplicateInfo['existing_entity_type'];
                $transaction['existing_description'] = $duplicateInfo['existing_description'];
                $transaction['existing_category_id'] = $duplicateInfo['existing_category_id'];
                $transaction['existing_files'] = $duplicateInfo['existing_files'];
            } else {
                $transaction['is_duplicate'] = false;
                $transaction['existing_files'] = [];
            }
        }

        return [
            'success' => true,
            'metadata' => $result['metadata'],
            'transactions' => $result['transactions'],
            'categories' => $categories,
        ];
    }

    /**
     * Import selected transactions from bank statement.
     *
     * @param FinancialFile $file The source file
     * @param array $transactions Validated transaction data
     * @param string $currency Currency code
     * @param array $transactionFiles Files indexed by transaction index
     * @return array Import results with counts
     */
    public function importTransactions(FinancialFile $file, array $transactions, string $currency = 'RON', array $transactionFiles = []): array
    {
        $organizationId = auth()->user()->organization_id;
        $userId = auth()->id();

        $importedExpenses = 0;
        $importedRevenues = 0;
        $skipped = 0;
        $filesUploaded = 0;

        DB::beginTransaction();
        try {
            foreach ($transactions as $index => $tx) {
                // Skip if not selected
                if (empty($tx['selected'])) {
                    $skipped++;
                    continue;
                }

                $date = \Carbon\Carbon::parse($tx['date']);

                // Truncate description to fit database column (255 chars max)
                $description = \Illuminate\Support\Str::limit($tx['description'], 250, '...');

                if ($tx['type'] === 'debit') {
                    // Create expense
                    $entity = FinancialExpense::create([
                        'organization_id' => $organizationId,
                        'user_id' => $userId,
                        'document_name' => $description,
                        'amount' => $tx['amount'],
                        'currency' => $currency,
                        'occurred_at' => $date,
                        'year' => $date->year,
                        'month' => $date->month,
                        'category_option_id' => $tx['category_id'] ?: null,
                        'note' => __('Importat din extras de cont: ') . $file->file_name,
                    ]);
                    $importedExpenses++;
                    $entityType = 'plata';
                } else {
                    // Create revenue
                    $entity = FinancialRevenue::create([
                        'organization_id' => $organizationId,
                        'user_id' => $userId,
                        'document_name' => $description,
                        'amount' => $tx['amount'],
                        'currency' => $currency,
                        'occurred_at' => $date,
                        'year' => $date->year,
                        'month' => $date->month,
                        'note' => __('Importat din extras de cont: ') . $file->file_name,
                    ]);
                    $importedRevenues++;
                    $entityType = 'incasare';
                }

                // Upload files for this transaction
                if (!empty($transactionFiles[$index])) {
                    foreach ($transactionFiles[$index] as $uploadedFile) {
                        $this->uploadFileToEntity($uploadedFile, $entity, $entityType, $date);
                        $filesUploaded++;
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'imported_expenses' => $importedExpenses,
                'imported_revenues' => $importedRevenues,
                'skipped' => $skipped,
                'files_uploaded' => $filesUploaded,
                'message' => $this->buildImportMessage($importedExpenses, $importedRevenues, $skipped, $filesUploaded),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => __('Eroare la import: ') . $e->getMessage(),
            ];
        }
    }

    /**
     * Upload a file and attach it to an entity (expense or revenue).
     */
    protected function uploadFileToEntity($uploadedFile, $entity, string $entityType, \Carbon\Carbon $date): void
    {
        $organizationId = auth()->user()->organization_id;
        $userId = auth()->id();

        // Allowed extensions
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        if (!in_array($extension, $allowedExtensions)) {
            return; // Skip invalid files
        }

        // Romanian month names for folder structure (format: MM-MonthName)
        $romanianMonths = [
            1 => '01-Ianuarie', 2 => '02-Februarie', 3 => '03-Martie', 4 => '04-Aprilie',
            5 => '05-Mai', 6 => '06-Iunie', 7 => '07-Iulie', 8 => '08-August',
            9 => '09-Septembrie', 10 => '10-Octombrie', 11 => '11-Noiembrie', 12 => '12-Decembrie'
        ];

        // Build folder path: year/month/type
        $folderType = $entityType === 'plata' ? 'Plati' : 'Incasari';
        $folderPath = $date->year . '/' . $romanianMonths[$date->month] . '/' . $folderType;

        // Generate friendly filename: DD.MM - DocumentName.ext
        $docName = preg_replace('/[^\w\s\-\.\(\)]/u', '', $entity->document_name);
        $docName = trim(substr($docName, 0, 100)); // Limit length
        $baseFilename = $date->format('d.m') . ' - ' . $docName;
        $filename = $baseFilename . '.' . $extension;

        // Check for duplicates and add counter
        $counter = 1;
        while (Storage::disk('financial')->exists($folderPath . '/' . $filename)) {
            $filename = $baseFilename . ' (' . $counter . ').' . $extension;
            $counter++;
        }

        // Store the file
        $path = $uploadedFile->storeAs($folderPath, $filename, 'financial');

        // Create the FinancialFile record with the friendly filename
        FinancialFile::create([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'file_name' => $filename, // Use generated friendly name, not original
            'file_path' => $path,
            'file_type' => $uploadedFile->getMimeType(),
            'mime_type' => $uploadedFile->getMimeType(),
            'file_size' => $uploadedFile->getSize(),
            'entity_type' => get_class($entity),
            'entity_id' => $entity->id,
            'an' => $date->year,
            'luna' => $date->month,
            'tip' => $entityType,
        ]);
    }

    /**
     * Get expense categories with hierarchy for the dropdown.
     */
    public function getExpenseCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return SettingOption::where('category', 'expense_categories')
            ->where(function ($q) {
                $q->whereNull('organization_id')
                  ->orWhere('organization_id', auth()->user()->organization_id);
            })
            ->where('is_active', true)
            ->whereNull('parent_id') // Only get root categories
            ->with(['children' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get existing transactions for duplicate detection.
     */
    public function getExistingTransactions(int $year, int $month, string $currency): array
    {
        $organizationId = auth()->user()->organization_id;

        $expenses = FinancialExpense::where('organization_id', $organizationId)
            ->where('year', $year)
            ->where('month', $month)
            ->where('currency', $currency)
            ->with(['files' => function ($q) {
                $q->select('id', 'entity_type', 'entity_id', 'file_name', 'file_path', 'file_type');
            }])
            ->get(['id', 'occurred_at', 'amount', 'document_name', 'category_option_id'])
            ->map(fn($e) => [
                'id' => $e->id,
                'entity_type' => FinancialExpense::class,
                'date' => $e->occurred_at->format('Y-m-d'),
                'amount' => (float) $e->amount,
                'description' => strtolower($e->document_name),
                'original_description' => $e->document_name,
                'type' => 'debit',
                'category_id' => $e->category_option_id,
                'files' => $e->files->map(fn($f) => [
                    'id' => $f->id,
                    'name' => $f->file_name,
                    'path' => $f->file_path,
                    'type' => $f->file_type,
                ])->toArray(),
            ])
            ->toArray();

        $revenues = FinancialRevenue::where('organization_id', $organizationId)
            ->where('year', $year)
            ->where('month', $month)
            ->where('currency', $currency)
            ->with(['files' => function ($q) {
                $q->select('id', 'entity_type', 'entity_id', 'file_name', 'file_path', 'file_type');
            }])
            ->get(['id', 'occurred_at', 'amount', 'document_name'])
            ->map(fn($r) => [
                'id' => $r->id,
                'entity_type' => FinancialRevenue::class,
                'date' => $r->occurred_at->format('Y-m-d'),
                'amount' => (float) $r->amount,
                'description' => strtolower($r->document_name),
                'original_description' => $r->document_name,
                'type' => 'credit',
                'category_id' => null,
                'files' => $r->files->map(fn($f) => [
                    'id' => $f->id,
                    'name' => $f->file_name,
                    'path' => $f->file_path,
                    'type' => $f->file_type,
                ])->toArray(),
            ])
            ->toArray();

        return array_merge($expenses, $revenues);
    }

    /**
     * Check if transaction is a duplicate and return match info.
     */
    public function findDuplicateInfo(array $transaction, array $existingTransactions): ?array
    {
        foreach ($existingTransactions as $existing) {
            // Same date and amount is likely a duplicate
            if ($existing['date'] === $transaction['date'] &&
                abs($existing['amount'] - $transaction['amount']) < 0.01 &&
                $existing['type'] === $transaction['type']) {
                return [
                    'is_duplicate' => true,
                    'existing_id' => $existing['id'],
                    'existing_entity_type' => $existing['entity_type'],
                    'existing_description' => $existing['original_description'],
                    'existing_category_id' => $existing['category_id'],
                    'existing_files' => $existing['files'] ?? [],
                ];
            }
        }
        return null;
    }

    /**
     * Build the import result message.
     */
    protected function buildImportMessage(int $expenses, int $revenues, int $skipped, int $filesUploaded = 0): string
    {
        $message = __('Import finalizat: :expenses cheltuieli si :revenues venituri importate.', [
            'expenses' => $expenses,
            'revenues' => $revenues,
        ]);

        if ($filesUploaded > 0) {
            $message .= ' ' . __(':files fisiere atasate.', ['files' => $filesUploaded]);
        }

        if ($skipped > 0) {
            $message .= ' ' . __(':skipped tranzactii omise.', ['skipped' => $skipped]);
        }

        return $message;
    }
}
