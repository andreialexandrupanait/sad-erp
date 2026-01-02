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

        // Check for existing transactions to detect duplicates
        $existingTransactions = $this->getExistingTransactions(
            $file->an,
            $file->luna,
            $result['metadata']['currency'] ?? 'RON'
        );

        // Mark duplicates in transactions and get existing category
        foreach ($result['transactions'] as &$transaction) {
            $duplicateInfo = $this->findDuplicateInfo($transaction, $existingTransactions);
            if ($duplicateInfo) {
                $transaction['is_duplicate'] = true;
                $transaction['existing_description'] = $duplicateInfo['existing_description'];
                $transaction['existing_category_id'] = $duplicateInfo['existing_category_id'];
            } else {
                $transaction['is_duplicate'] = false;
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
     * @return array Import results with counts
     */
    public function importTransactions(FinancialFile $file, array $transactions, string $currency = 'RON'): array
    {
        $organizationId = auth()->user()->organization_id;
        $userId = auth()->id();

        $importedExpenses = 0;
        $importedRevenues = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($transactions as $tx) {
                // Skip if not selected
                if (empty($tx['selected'])) {
                    $skipped++;
                    continue;
                }

                $date = \Carbon\Carbon::parse($tx['date']);

                if ($tx['type'] === 'debit') {
                    // Create expense
                    FinancialExpense::create([
                        'organization_id' => $organizationId,
                        'user_id' => $userId,
                        'document_name' => $tx['description'],
                        'amount' => $tx['amount'],
                        'currency' => $currency,
                        'occurred_at' => $date,
                        'year' => $date->year,
                        'month' => $date->month,
                        'category_option_id' => $tx['category_id'] ?: null,
                        'note' => __('Importat din extras de cont: ') . $file->file_name,
                    ]);
                    $importedExpenses++;
                } else {
                    // Create revenue
                    FinancialRevenue::create([
                        'organization_id' => $organizationId,
                        'user_id' => $userId,
                        'document_name' => $tx['description'],
                        'amount' => $tx['amount'],
                        'currency' => $currency,
                        'occurred_at' => $date,
                        'year' => $date->year,
                        'month' => $date->month,
                        'note' => __('Importat din extras de cont: ') . $file->file_name,
                    ]);
                    $importedRevenues++;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'imported_expenses' => $importedExpenses,
                'imported_revenues' => $importedRevenues,
                'skipped' => $skipped,
                'message' => $this->buildImportMessage($importedExpenses, $importedRevenues, $skipped),
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
            ->get(['occurred_at', 'amount', 'document_name', 'category_option_id'])
            ->map(fn($e) => [
                'date' => $e->occurred_at->format('Y-m-d'),
                'amount' => (float) $e->amount,
                'description' => strtolower($e->document_name),
                'original_description' => $e->document_name,
                'type' => 'debit',
                'category_id' => $e->category_option_id,
            ])
            ->toArray();

        $revenues = FinancialRevenue::where('organization_id', $organizationId)
            ->where('year', $year)
            ->where('month', $month)
            ->where('currency', $currency)
            ->get(['occurred_at', 'amount', 'document_name'])
            ->map(fn($r) => [
                'date' => $r->occurred_at->format('Y-m-d'),
                'amount' => (float) $r->amount,
                'description' => strtolower($r->document_name),
                'original_description' => $r->document_name,
                'type' => 'credit',
                'category_id' => null,
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
                    'existing_description' => $existing['original_description'],
                    'existing_category_id' => $existing['category_id'],
                ];
            }
        }
        return null;
    }

    /**
     * Build the import result message.
     */
    protected function buildImportMessage(int $expenses, int $revenues, int $skipped): string
    {
        $message = __('Import finalizat: :expenses cheltuieli si :revenues venituri importate.', [
            'expenses' => $expenses,
            'revenues' => $revenues,
        ]);

        if ($skipped > 0) {
            $message .= ' ' . __(':skipped tranzactii omise.', ['skipped' => $skipped]);
        }

        return $message;
    }
}
