<?php

namespace App\Services\Financial\Import;

/**
 * SmartBill Data Mapper Service
 *
 * Handles SmartBill column mapping and data transformation.
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

        // Amount variations - SmartBill can use different column names
        'Total' => 'amount', 'Valoare' => 'amount', 'Suma' => 'amount',
        'Total factura' => 'amount', 'Valoare totala' => 'amount',
        'Total factură' => 'amount', 'Valoare totală' => 'amount',
        'Incasat' => 'amount', 'Încasat' => 'amount',
        'Total de plata' => 'amount', 'Total de plată' => 'amount',
        'Valoare fara TVA' => 'amount', 'Valoare fără TVA' => 'amount',
        'Total cu TVA' => 'amount',

        // Currency
        'Moneda' => 'currency', 'Monedă' => 'currency',

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
        $indicators = ['Serie', 'Factura', 'CIF', 'Data incasarii'];
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
        // SmartBill format: "SAD0568" where "SAD" is serie and "0568" is numar
        if (!empty($mapped['document_name']) && (empty($mapped['serie']) || empty($mapped['numar']))) {
            $docName = trim($mapped['document_name']);
            // Pattern: letters followed by digits (e.g., SAD0568, PROF123, AB12345)
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

        return $mapped;
    }
}
