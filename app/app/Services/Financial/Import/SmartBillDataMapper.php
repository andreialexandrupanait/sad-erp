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
        'Serie' => 'serie', 'Numar' => 'numar', 'Factura' => 'document_name',
        'Data' => 'occurred_at', 'Data incasarii' => 'occurred_at',
        'Total' => 'amount', 'Moneda' => 'currency',
        'Client' => 'client_name', 'CIF' => 'cif_client',
        'Adresa' => 'client_address', 'Persoana contact' => 'client_contact',
        'Observatii' => 'note',
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
            $mapped[self::COLUMN_MAP[$key] ?? $key] = $value;
        }

        // Create document_name from Serie + Numar
        if (empty($mapped['document_name']) && !empty($mapped['serie']) && !empty($mapped['numar'])) {
            $mapped['document_name'] = trim($mapped['serie']) . '-' . trim($mapped['numar']);
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
