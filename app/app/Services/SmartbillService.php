<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SmartbillService
{
    protected $username;
    protected $token;
    protected $cif;
    protected $baseUrl = 'https://ws.smartbill.ro:8183/SBORO/api';

    public function __construct($username = null, $token = null, $cif = null)
    {
        $this->username = $username ?? config('smartbill.username');
        $this->token = $token ?? config('smartbill.token');
        $this->cif = $cif ?? config('smartbill.vatCode');
    }

    /**
     * Get the authorization header
     */
    protected function getAuthHeader()
    {
        return 'Basic ' . base64_encode($this->username . ':' . $this->token);
    }

    /**
     * Make an API call to Smartbill
     */
    protected function makeRequest($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;

        try {
            $request = Http::withHeaders([
                'Authorization' => $this->getAuthHeader(),
                'Accept' => 'application/json',
            ]);

            if ($method === 'GET') {
                $response = $request->get($url, $data ?? []);
            } elseif ($method === 'POST') {
                $response = $request->post($url, $data ?? []);
            } else {
                throw new Exception("Unsupported HTTP method: {$method}");
            }

            if (!$response->successful()) {
                Log::error('Smartbill API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $url,
                ]);
                throw new Exception('Smartbill API Error: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Smartbill API Exception', [
                'message' => $e->getMessage(),
                'url' => $url,
            ]);
            throw $e;
        }
    }

    /**
     * List invoices for a given period
     *
     * IMPORTANT: Smartbill Cloud REST API v1 does NOT have a "list invoices" endpoint.
     * The API is designed for:
     * - Creating invoices
     * - Getting specific invoices (if you know series + number)
     * - Downloading PDFs (if you know series + number)
     * - Deleting/canceling invoices
     *
     * To import existing invoices, you need to:
     * 1. Export invoices from Smartbill web interface as CSV/Excel
     * 2. Parse the CSV file
     * 3. For each invoice in CSV, fetch details via API using getInvoice()
     *
     * This method is kept for potential future API updates.
     */
    public function listInvoices($fromDate, $toDate, $page = 1, $perPage = 50)
    {
        throw new Exception(
            'Smartbill API does not support listing invoices. ' .
            'Please export invoices from Smartbill web interface (Rapoarte > Export) ' .
            'and use the CSV import feature instead. ' .
            'Alternatively, provide a list of invoice series and numbers to import specific invoices.'
        );
    }

    /**
     * Import invoices from CSV data exported from Smartbill
     *
     * @param array $csvData Array of invoice data from CSV export
     * @return array List of invoice details fetched from API
     */
    public function importFromCsvData($csvData)
    {
        $invoices = [];

        foreach ($csvData as $row) {
            $series = $row['series'] ?? $row['Serie'] ?? null;
            $number = $row['number'] ?? $row['Numar'] ?? null;

            if (!$series || !$number) {
                continue;
            }

            try {
                $invoice = $this->getInvoice($series, $number);
                if ($invoice) {
                    $invoices[] = $invoice;
                }
            } catch (Exception $e) {
                Log::warning('Failed to fetch invoice from Smartbill', [
                    'series' => $series,
                    'number' => $number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return ['list' => $invoices];
    }

    /**
     * Get invoice details
     */
    public function getInvoice($seriesName, $number)
    {
        $endpoint = sprintf('/invoice?cif=%s&seriesname=%s&number=%s',
            urlencode($this->cif),
            urlencode($seriesName),
            urlencode($number)
        );

        return $this->makeRequest('GET', $endpoint);
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoicePdf($seriesName, $number)
    {
        $url = sprintf(
            '%s/invoice/pdf?cif=%s&seriesname=%s&number=%s',
            $this->baseUrl,
            urlencode($this->cif),
            urlencode($seriesName),
            urlencode($number)
        );

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->getAuthHeader(),
                'Accept' => 'application/octet-stream',
            ])->get($url);

            if (!$response->successful()) {
                Log::error('Smartbill PDF Download Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $url,
                ]);
                return null;
            }

            return $response->body();
        } catch (Exception $e) {
            Log::error('Smartbill PDF Download Exception', [
                'message' => $e->getMessage(),
                'url' => $url,
            ]);
            return null;
        }
    }

    /**
     * Get payment status for an invoice
     */
    public function getInvoicePaymentStatus($seriesName, $number)
    {
        $endpoint = sprintf('/invoice/paymentstatus?cif=%s&seriesname=%s&number=%s',
            urlencode($this->cif),
            urlencode($seriesName),
            urlencode($number)
        );

        return $this->makeRequest('GET', $endpoint);
    }

    /**
     * Test connection to Smartbill API
     */
    public function testConnection()
    {
        try {
            $result = $this->makeRequest('GET', '/series?cif=' . urlencode($this->cif) . '&type=f');
            return [
                'success' => true,
                'message' => 'Successfully connected to Smartbill API',
                'data' => $result,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to connect to Smartbill API: ' . $e->getMessage(),
            ];
        }
    }
}
