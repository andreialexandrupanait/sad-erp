<?php

namespace App\Services\Financial\Import;

/**
 * SmartBill Data Mapper Service
 *
 * Handles SmartBill column mapping and data transformation.
 * Updated to support dual currency columns (EUR + RON).
 */
class SmartBillDataMapper
{
    private const COLUMN_MAP = [
        // Invoice identifiers
        'Serie' => 'serie', 'Numar' => 'numar', 'Factura' => 'document_name',
        'Nr.' => 'numar', 'Număr' => 'numar', 'Nr' => 'numar',

        // Dates
        'Data' => 'occurred_at', 'Data incasarii' => 'occurred_at',
        'Data facturii' => 'occurred_at', 'Data emiterii' => 'occurred_at',
        'Dată' => 'occurred_at',

        // RON amount (primary) - SmartBill provides converted RON values
        'Total Value(RON)' => 'amount_ron',
        'Total Value (RON)' => 'amount_ron',
        'Valoare(RON)' => 'amount_ron',
        'Valoare (RON)' => 'amount_ron',
        'Total(RON)' => 'amount_ron',
        'Total (RON)' => 'amount_ron',

        // Original currency amount (for EUR reference)
        'Total Value' => 'amount_original',

        // Legacy amount mappings (when no dual columns exist)
        'Total' => 'amount', 'Valoare' => 'amount', 'Suma' => 'amount',
        'Total factura' => 'amount', 'Valoare totala' => 'amount',
        'Total factură' => 'amount', 'Valoare totală' => 'amount',
        'Incasat' => 'amount', 'Încasat' => 'amount',
        'Total de plata' => 'amount', 'Total de plată' => 'amount',
        'Valoare fara TVA' => 'amount', 'Valoare fără TVA' => 'amount',
        'Total cu TVA' => 'amount',

        // Currency
        'Moneda' => 'currency', 'Monedă' => 'currency', 'Currency' => 'currency',

        // Exchange rate (if provided by SmartBill)
        'Curs' => 'exchange_rate', 'Curs valutar' => 'exchange_rate',
        'Exchange Rate' => 'exchange_rate',

        // Client info
        'Client' => 'client_name', 'Denumire client' => 'client_name',
        'Nume client' => 'client_name', 'Furnizor' => 'client_name',
        'CIF' => 'cif_client', 'CIF client' => 'cif_client', 'CUI' => 'cif_client',
        'Adresa' => 'client_address', 'Adresă' => 'client_address',
        'Persoana contact' => 'client_contact',

        // Notes
        'Observatii' => 'note', 'Observații' => 'note', 'Mentiuni' => 'note',
    ];

    public function isSmartBillExport(array $header): bool
    {
        $indicators = ['Serie', 'Factura', 'CIF', 'Data incasarii', 'Total Value(RON)'];
        foreach ($indicators as $col) {
            if (in_array($col, $header)) return true;
        }
        return false;
    }

    public function mapColumns(array $data): array
    {
        $mapped = [];
        foreach ($data as $key => $value) {
            // Try exact match first
            if (isset(self::COLUMN_MAP[$key])) {
                $mapped[self::COLUMN_MAP[$key]] = $value;
            } else {
                // Try case-insensitive match
                $found = false;
                foreach (self::COLUMN_MAP as $colName => $mappedName) {
                    if (strcasecmp(trim($key), trim($colName)) === 0) {
                        $mapped[$mappedName] = $value;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $mapped[$key] = $value;
                }
            }
        }

        // Create document_name from Serie + Numar
        if (empty($mapped['document_name']) && !empty($mapped['serie']) && !empty($mapped['numar'])) {
            $mapped['document_name'] = trim($mapped['serie']) . '-' . trim($mapped['numar']);
        }

        // If we have document_name but no serie/numar, try to extract them
        if (!empty($mapped['document_name']) && (empty($mapped['serie']) || empty($mapped['numar']))) {
            $docName = trim($mapped['document_name']);
            if (preg_match('/^([A-Za-z]+)(\d+)$/', $docName, $matches)) {
                if (empty($mapped['serie'])) {
                    $mapped['serie'] = $matches[1];
                }
                if (empty($mapped['numar'])) {
                    $mapped['numar'] = $matches[2];
                }
            }
        }

        // Default currency
        if (empty($mapped['currency'])) $mapped['currency'] = 'RON';

        // Convert date DD/MM/YYYY to YYYY-MM-DD
        if (!empty($mapped['occurred_at']) && preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', trim($mapped['occurred_at']), $m)) {
            $mapped['occurred_at'] = $m[3] . '-' . $m[2] . '-' . $m[1];
        }

        // Handle dual currency columns from SmartBill
        // If we have both amount_ron and amount_original, use them appropriately
        if (!empty($mapped['amount_ron']) && !empty($mapped['amount_original'])) {
            // SmartBill provides both - use RON as primary amount
            $mapped['amount'] = $mapped['amount_ron'];
            
            // If currency is EUR, store original as amount_eur
            if (strtoupper($mapped['currency'] ?? 'RON') === 'EUR') {
                $mapped['amount_eur'] = $mapped['amount_original'];
            }
        } elseif (!empty($mapped['amount_ron']) && empty($mapped['amount'])) {
            // Only RON value provided
            $mapped['amount'] = $mapped['amount_ron'];
        }

        // Clean up temporary mapping keys
        unset($mapped['amount_ron'], $mapped['amount_original']);

        return $mapped;
    }
}
