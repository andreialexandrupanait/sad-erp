<?php

namespace App\Services\Banking;

use App\Models\ExpenseCategoryMapping;
use Smalot\PdfParser\Parser;

class BankStatementPdfParser
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Parse a Banca Transilvania PDF statement
     *
     * @param string $pdfPath Path to the PDF file
     * @return array Array with 'transactions', 'metadata', and 'errors'
     */
    public function parse(string $pdfPath): array
    {
        $result = [
            'transactions' => [],
            'metadata' => [],
            'errors' => [],
        ];

        try {
            $pdf = $this->parser->parseFile($pdfPath);
            $text = $pdf->getText();

            // Extract metadata
            $result['metadata'] = $this->extractMetadata($text);

            // Extract transactions
            $result['transactions'] = $this->extractTransactions($text);

        } catch (\Exception $e) {
            $result['errors'][] = 'Failed to parse PDF: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Extract metadata from statement (account, period, etc.)
     */
    private function extractMetadata(string $text): array
    {
        $metadata = [
            'account_name' => null,
            'client_id' => null,
            'cif' => null,
            'iban' => null,
            'currency' => 'RON',
            'period_start' => null,
            'period_end' => null,
            'opening_balance' => null,
            'closing_balance' => null,
        ];

        // Extract company name (first line before "Client:")
        if (preg_match('/^([A-Z][A-Z\s]+S\s*R\s*L)\s+Client:/m', $text, $matches)) {
            $metadata['account_name'] = trim(str_replace(['  ', '   '], ' ', $matches[1]));
        }

        // Extract Client ID
        if (preg_match('/Client:\s*(\d+)/i', $text, $matches)) {
            $metadata['client_id'] = $matches[1];
        }

        // Extract CUI/CIF
        if (preg_match('/CUI:\s*(\d+)/i', $text, $matches)) {
            $metadata['cif'] = $matches[1];
        }

        // Extract IBAN
        if (preg_match('/Cod IBAN:\s*(RO\d{2}[A-Z]{4}[A-Z0-9]+)/i', $text, $matches)) {
            $metadata['iban'] = $matches[1];
        }

        // Extract period from "EXTRAS CONT ... din DD/MM/YYYY - DD/MM/YYYY"
        if (preg_match('/din\s+(\d{2}\/\d{2}\/\d{4})\s*-\s*(\d{2}\/\d{2}\/\d{4})/i', $text, $matches)) {
            $metadata['period_start'] = $this->parseDate($matches[1]);
            $metadata['period_end'] = $this->parseDate($matches[2]);
        }

        // Extract opening balance (SOLD ANTERIOR)
        if (preg_match('/SOLD ANTERIOR\s+([\d\.,]+)/i', $text, $matches)) {
            $metadata['opening_balance'] = $this->parseAmount($matches[1]);
        }

        // Extract closing balance (SOLD FINAL CONT)

        // Detect currency from IBAN suffix, Valuta field, or text patterns
        // BT EUR accounts have EURCRT in IBAN, RON accounts have RONCRT
        if (!empty($metadata['iban'])) {
            if (preg_match('/EUR/', $metadata['iban'])) {
                $metadata['currency'] = 'EUR';
            } elseif (preg_match('/USD/', $metadata['iban'])) {
                $metadata['currency'] = 'USD';
            }
        }

        // Also check for explicit currency text in statement (account-level, not transaction-level)
        if (preg_match('/Valuta\s*[:\-]?\s*(EUR|USD|RON)/i', $text, $matches)) {
            $metadata['currency'] = strtoupper($matches[1]);
        }

        // Check for "Cont curent EUR" or similar patterns (account-level indicator)
        if (preg_match('/Cont\s+curent\s+(EUR|USD)/i', $text, $matches)) {
            $metadata['currency'] = strtoupper($matches[1]);
        }

        // Check for "EXTRAS DE CONT IN EUR" or similar header patterns
        if (preg_match('/EXTRAS\s+(?:DE\s+)?CONT\s+(?:IN\s+)?(EUR|USD)/i', $text, $matches)) {
            $metadata['currency'] = strtoupper($matches[1]);
        }
        if (preg_match('/SOLD FINAL CONT\s+([\d\.,]+)/i', $text, $matches)) {
            $metadata['closing_balance'] = $this->parseAmount($matches[1]);
        }

        return $metadata;
    }

    /**
     * Extract transactions from the statement
     *
     * BT format has transactions like:
     * DD/MM/YYYYDescription text here
     * More description text;
     * REF: XXXXX
     * 123.45      (amount with trailing spaces - debit if left column, credit if right)
     *
     * Multiple transactions can share the same date line - subsequent transactions
     * start with transaction type keywords without a date.
     */
    private function extractTransactions(string $text): array
    {
        $transactions = [];

        // Split text into lines
        $lines = explode("\n", $text);
        $currentDate = null;
        $lastKnownDate = null;
        $currentDescription = '';
        $currentDebit = null;
        $currentCredit = null;
        $inTransaction = false;

        // Transaction type keywords that can start a new transaction
        $transactionStarters = [
            'Plata OP intra',
            'Plata OP inter',
            'Plata la POS',
            'Incasare OP',
            'Incasare Instant',
            'Pachet IZI',
            'Schimb valutar',
            'Comision',
            'Returnare',
            'Transfer intern',
        ];
        $starterPattern = '/^(' . implode('|', array_map('preg_quote', $transactionStarters)) . ')/i';

        foreach ($lines as $lineIndex => $line) {
            $trimmedLine = trim($line);

            // Skip empty lines
            if (empty($trimmedLine)) {
                continue;
            }

            // Skip header/footer lines
            if ($this->isHeaderFooterLine($trimmedLine)) {
                continue;
            }

            // Skip summary lines - but save current transaction first
            if ($this->isSummaryLine($trimmedLine)) {
                if ($inTransaction && $currentDate && ($currentDebit || $currentCredit)) {
                    $transactions[] = $this->createTransaction($currentDate, $currentDescription, $currentDebit, $currentCredit);
                }
                $inTransaction = false;
                $currentDescription = '';
                $currentDebit = null;
                $currentCredit = null;
                continue;
            }

            // Check if line starts with a date (DD/MM/YYYY) - may or may not have space after
            if (preg_match('/^(\d{2}\/\d{2}\/\d{4})\s*(.*)/', $trimmedLine, $matches)) {
                // Save previous transaction if exists
                if ($inTransaction && $currentDate && ($currentDebit || $currentCredit)) {
                    $transactions[] = $this->createTransaction($currentDate, $currentDescription, $currentDebit, $currentCredit);
                }

                // Start new transaction
                $currentDate = $this->parseDate($matches[1]);
                $lastKnownDate = $currentDate;
                $rest = trim($matches[2]);
                $currentDescription = $rest;
                $currentDebit = null;
                $currentCredit = null;
                $inTransaction = true;
                continue;
            }

            // Check if this line starts a new transaction (without date) using transaction keywords
            if ($lastKnownDate && preg_match($starterPattern, $trimmedLine)) {
                // Save previous transaction if exists
                if ($inTransaction && $currentDate && ($currentDebit || $currentCredit)) {
                    $transactions[] = $this->createTransaction($currentDate, $currentDescription, $currentDebit, $currentCredit);
                }

                // Start new transaction with last known date
                $currentDate = $lastKnownDate;
                $currentDescription = $trimmedLine;
                $currentDebit = null;
                $currentCredit = null;
                $inTransaction = true;
                continue;
            }

            // Check if this is an amount line (number with optional surrounding spaces)
            // BT format: "877.00      " for debit or "      253.02" for credit
            // Use ORIGINAL line (not trimmed) to detect column position
            $originalLine = $lines[$lineIndex];
            if ($inTransaction && preg_match('/^\s*([\d\.,]+)\s*$/', $trimmedLine, $matches)) {
                $amount = $this->parseAmount($matches[1]);
                if ($amount !== null) {
                    // Determine if debit or credit based on leading whitespace
                    // Credit column has significant leading spaces (5+)
                    $leadingSpaces = strlen($originalLine) - strlen(ltrim($originalLine));

                    if ($leadingSpaces >= 5) {
                        $currentCredit = $amount;
                    } else {
                        $currentDebit = $amount;
                    }
                }
                continue;
            }

            // If we're in a transaction, accumulate description
            if ($inTransaction) {
                // Check if line is a REF line
                if (preg_match('/REF:\s*\S+/', $trimmedLine)) {
                    // Skip REF lines from description
                    continue;
                }

                // Add to description
                if (!empty($currentDescription)) {
                    $currentDescription .= ' ' . $trimmedLine;
                } else {
                    $currentDescription = $trimmedLine;
                }
            }
        }

        // Don't forget the last transaction
        if ($inTransaction && $currentDate && ($currentDebit || $currentCredit)) {
            $transactions[] = $this->createTransaction($currentDate, $currentDescription, $currentDebit, $currentCredit);
        }

        return $transactions;
    }

    /**
     * Create a transaction array
     */
    private function createTransaction(?string $date, string $description, ?float $debit, ?float $credit): array
    {
        $description = $this->cleanDescription($description);

        // Extract RON equivalent for foreign currency transactions
        $ronEquivalent = null;
        if (preg_match('/ECHIVALENT\s+LEI\s+([\d\.,]+)/i', $description, $matches)) {
            $ronEquivalent = $this->parseAmount($matches[1]);
        }

        return [
            'date' => $date,
            'description' => $description,
            'debit' => $debit,
            'credit' => $credit,
            'amount' => $debit ?? $credit,
            'type' => $debit ? 'debit' : 'credit',
            'suggested_category' => $debit ? ExpenseCategoryMapping::findCategoryForDescription($description) : null,
            'reference' => $this->extractReference($description),
            'ron_equivalent' => $ronEquivalent,
        ];
    }

    /**
     * Clean up description text
     */
    private function cleanDescription(string $description): string
    {
        // Remove multiple spaces
        $description = preg_replace('/\s+/', ' ', $description);

        // Remove reference numbers
        $description = preg_replace('/\s*REF:\s*\S+\s*/', '', $description);

        // Remove common BT prefixes to get cleaner descriptions
        $description = preg_replace('/^Plata OP intra - canal electronic\s*/i', '', $description);
        $description = preg_replace('/^Plata la POS non-BT cu card VISA\s*/i', 'POS ', $description);
        $description = preg_replace('/^Incasare OP - canal electronic\s*/i', '', $description);
        $description = preg_replace('/^Incasare Instant\s*/i', '', $description);
        $description = preg_replace('/^Pachet IZI\s*/i', 'Pachet IZI ', $description);

        // Clean up EPOS descriptions
        $description = preg_replace('/EPOS \d{2}\/\d{2}\/\d{4}\s+\d+\s+TID:\d+\s+/', '', $description);

        // Remove trailing semicolons and spaces
        $description = rtrim($description, '; ');

        // Extract meaningful part from complex descriptions
        // e.g., "Salarii Mai;1;Rusu Nicoleta;RO28BTRL..." -> "Salarii Mai - Rusu Nicoleta"
        if (preg_match('/^([^;]+);[^;]*;([^;]+);/', $description, $matches)) {
            $description = trim($matches[1]) . ' - ' . trim($matches[2]);
        }

        return trim($description);
    }

    /**
     * Extract reference number from description
     */
    private function extractReference(string $description): ?string
    {
        if (preg_match('/REF:\s*(\S+)/', $description, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Check if line is a header/footer line to skip
     */
    private function isHeaderFooterLine(string $line): bool
    {
        $skipPatterns = [
            '/^BANCA TRANSILVANIA/i',
            '/^Info clienti/i',
            '/^Solicitant/i',
            '/^Tiparit/i',
            '/^Informatii noi/i',
            '/^Comisioanele\s*aplicate/i',
            '/^Fondurile\s*pe\s*care/i',
            '/^EXTRAS CONT/i',
            '/^CONT\s+\d+/i',
            '/^Data\s+Descriere/i',
            '/^S\.A\./i',
            '/bancatransilvania\.ro/i',
            '/^Capitalul social/i',
            '/^\d+\s*\/\s*\d+\s*$/', // Page numbers like "1 / 4"
            '/^Valuta$/i',
            '/^Cont de disponibil/i',
            '/^RON$/i',
            '/^Debit\s+Credit$/i',
            '/^Client:/i',
            '/^CUI:/i',
            '/^Cod IBAN:/i',
            '/^[A-Z\s]+S\s*R\s*L\s+Client:/i', // Company name line
            '/^Numarul:/i',
            '/comision\s*Transfond/i',
            '/comision\s*BNR/i',
            '/GarantareaDepozitelor/i',
            '/garantarea-depozitelor/i',
        ];

        foreach ($skipPatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if line is a summary line (RULAJ, SOLD, etc.)
     */
    private function isSummaryLine(string $line): bool
    {
        $summaryPatterns = [
            '/^RULAJ\s+(ZI|TOTAL)/i',
            '/^SOLD\s+(ANTERIOR|FINAL)/i',
            '/^SUME BLOCATE/i',
            '/^TOTAL DISPONIBIL/i',
            '/^Fonduri proprii/i',
            '/^Credit neutilizat/i',
            '/^din care$/i',
            '/^La data curenta/i',
            '/^Acest extras/i',
            '/^\d{2}\/\d{2}\/\d{4}RULAJ/i', // Date followed by RULAJ
        ];

        foreach ($summaryPatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse date from DD/MM/YYYY format
     */
    private function parseDate(string $date): ?string
    {
        if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $date, $matches)) {
            return "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        }
        return null;
    }

    /**
     * Parse amount from string (handles thousand separators and decimals)
     * BT PDF format: 1,087.81 (comma for thousands, dot for decimals - US style)
     */
    private function parseAmount(string $amount): ?float
    {
        // Remove thousand separators (comma) - dot is decimal separator
        $amount = str_replace(',', '', $amount);

        $value = (float) $amount;
        return $value > 0 ? $value : null;
    }

    /**
     * Get only debit transactions (expenses)
     */
    public function getDebitTransactions(string $pdfPath): array
    {
        $result = $this->parse($pdfPath);

        $debits = array_filter($result['transactions'], function ($tx) {
            return $tx['type'] === 'debit' && $tx['debit'] > 0;
        });

        return [
            'transactions' => array_values($debits),
            'metadata' => $result['metadata'],
            'errors' => $result['errors'],
        ];
    }

    /**
     * Get only credit transactions (revenues)
     */
    public function getCreditTransactions(string $pdfPath): array
    {
        $result = $this->parse($pdfPath);

        $credits = array_filter($result['transactions'], function ($tx) {
            return $tx['type'] === 'credit' && $tx['credit'] > 0;
        });

        return [
            'transactions' => array_values($credits),
            'metadata' => $result['metadata'],
            'errors' => $result['errors'],
        ];
    }
}
