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
