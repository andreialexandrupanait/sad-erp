<?php

namespace App\Services\Financial\Import;

use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Import Validator Service - Validates revenue import rows
 */
class ImportValidator
{
    public function validate(array $data): array
    {
        // Check if this is a summary/total row that should be silently skipped
        if ($this->isSummaryRow($data)) {
            return [false, []]; // Empty errors = silent skip
        }

        $validator = Validator::make($data, [
            'document_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|in:RON,EUR',
            'occurred_at' => 'required|string',
        ]);

        if ($validator->fails()) {
            return [false, $validator->errors()->all()];
        }

        // Validate date format separately (accept multiple formats)
        if (!$this->isValidDate($data['occurred_at'])) {
            return [false, ['The occurred at field must be a valid date.']];
        }

        return [true, []];
    }

    /**
     * Check if this row is a summary/total row that should be silently skipped.
     * SmartBill exports often have total rows at the end.
     */
    private function isSummaryRow(array $data): bool
    {
        // No document name = likely a summary row
        $docName = trim($data['document_name'] ?? '');
        if (empty($docName)) {
            return true;
        }

        // Check if it's a "Total" or summary indicator
        $summaryIndicators = ['total', 'subtotal', 'suma', 'totaluri', 'grand total'];
        if (in_array(strtolower($docName), $summaryIndicators)) {
            return true;
        }

        // No date = likely a summary row
        $date = trim($data['occurred_at'] ?? '');
        if (empty($date)) {
            return true;
        }

        return false;
    }

    /**
     * Check if date string can be parsed
     * Supports multiple formats: Y-m-d, Y/m/d, d-m-Y, d/m/Y
     */
    private function isValidDate(string $date): bool
    {
        $formats = [
            'Y-m-d',     // 2023-12-15
            'Y/m/d',     // 2023/12/15
            'd-m-Y',     // 15-12-2023
            'd/m/Y',     // 15/12/2023
        ];

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $date);
                if ($parsed && $parsed->format($format) === $date) {
                    return true;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return false;
    }
}
