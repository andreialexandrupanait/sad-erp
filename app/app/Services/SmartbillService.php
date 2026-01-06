<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service for interacting with SmartBill API.
 *
 * SmartBill is a Romanian invoicing and accounting platform. This service handles:
 * - Authentication via Basic Auth
 * - Invoice retrieval and synchronization
 * - Client data synchronization
 * - Tax document management
 *
 * @link https://www.smartbill.ro SmartBill Platform
 * @link https://www.smartbill.ro/api/ SmartBill API Documentation
 */
class SmartbillService
{
    /** @var string SmartBill username for API authentication */
    protected $username;

    /** @var string SmartBill API token */
    protected $token;

    /** @var string Romanian Tax ID (CIF/VAT code) */
    protected $cif;

    /** @var string SmartBill API base URL */
    protected $baseUrl = 'https://ws.smartbill.ro:8183/SBORO/api';

    /**
     * Initialize SmartBill service with credentials.
     *
     * @param string|null $username SmartBill username (defaults to config)
     * @param string|null $token SmartBill API token (defaults to config)
     * @param string|null $cif Romanian CIF/VAT code (defaults to config)
     */
    public function __construct($username = null, $token = null, $cif = null)
    {
        $this->username = $username ?? config('smartbill.username');
        $this->token = $token ?? config('smartbill.token');
        $this->cif = $cif ?? config('smartbill.vatCode');
    }

    /**
     * Generate Basic Auth header for SmartBill API.
     *
     * @return string Base64-encoded Basic Auth header value
     */
    protected function getAuthHeader()
    {
        return 'Basic ' . base64_encode($this->username . ':' . $this->token);
    }

    /**
     * Make an authenticated HTTP request to SmartBill API.
     *
     * @param string $method HTTP method (GET or POST)
     * @param string $endpoint API endpoint path (e.g., '/invoice')
     * @param array|null $data Request payload for POST or query params for GET
     * @return array Decoded JSON response
     * @throws Exception If API request fails or returns error
     */
    protected function makeRequest($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;

        try {
            // Use retry with exponential backoff for transient failures
            $request = Http::withHeaders([
                'Authorization' => $this->getAuthHeader(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->retry(3, function (int $attempt, \Exception $exception) {
                // Exponential backoff: 1s, 2s, 4s
                return $attempt * 1000;
            }, function (\Exception $exception) {
                // Only retry on connection errors or 5xx server errors
                return $exception instanceof ConnectionException
                    || ($exception instanceof \Illuminate\Http\Client\RequestException
                        && $exception->response?->serverError());
            });

            if ($method === 'GET') {
                $response = $request->timeout(30)->get($url, $data ?? []);
            } elseif ($method === 'POST') {
                $response = $request->timeout(30)->post($url, $data ?? []);
            } else {
                throw new Exception("Unsupported HTTP method: {$method}");
            }

            $responseData = $response->json();

            // Check for Smartbill-specific error in response body
            if (isset($responseData['errorText']) && !empty($responseData['errorText'])) {
                throw new Exception($responseData['errorText']);
            }

            if (isset($responseData['successfully']) && $responseData['successfully'] === false) {
                $errorMessage = $responseData['errorText'] ?? 'Unknown Smartbill error';
                throw new Exception($errorMessage);
            }

            if (!$response->successful()) {
                Log::error('Smartbill API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $url,
                ]);
                throw new Exception('Smartbill API Error: HTTP ' . $response->status());
            }

            return $responseData;
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
     */
    public function listInvoices(string $fromDate, string $toDate, int $page = 1, int $perPage = 50): never
    {
        throw new Exception(
            'Smartbill API does not support listing invoices. ' .
            'Please export invoices from Smartbill web interface (Rapoarte > Export) ' .
            'and use the CSV import feature instead.'
        );
    }

    /**
     * Import invoices from CSV data exported from Smartbill
     */
    public function importFromCsvData(array $csvData): array
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
    public function getInvoice(string $seriesName, string $number): ?array
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
    public function downloadInvoicePdf(string $seriesName, string $number): ?string
    {
        $url = sprintf(
            '%s/invoice/pdf?cif=%s&seriesname=%s&number=%s',
            $this->baseUrl,
            urlencode($this->cif),
            urlencode($seriesName),
            urlencode($number)
        );

        Log::info('Smartbill PDF Download attempt', [
            'series' => $seriesName,
            'number' => $number,
            'cif' => $this->cif,
            'url' => $url,
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->getAuthHeader(),
                'Accept' => 'application/octet-stream',
            ])->timeout(30)->get($url);

            Log::info('Smartbill PDF response', [
                'status' => $response->status(),
                'content_type' => $response->header('Content-Type'),
                'body_length' => strlen($response->body()),
                'is_pdf' => str_starts_with($response->body(), '%PDF'),
            ]);

            if (!$response->successful()) {
                Log::error('Smartbill PDF Download Error', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500),
                    'url' => $url,
                ]);
                return null;
            }

            // Check if response is actually a PDF
            $body = $response->body();
            if (!str_starts_with($body, '%PDF')) {
                Log::warning('Smartbill PDF response is not a PDF', [
                    'content_preview' => substr($body, 0, 200),
                    'series' => $seriesName,
                    'number' => $number,
                ]);
                return null;
            }

            Log::info('Smartbill PDF downloaded successfully', [
                'series' => $seriesName,
                'number' => $number,
                'size' => strlen($body),
            ]);

            return $body;
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
    public function getInvoicePaymentStatus(string $seriesName, string $number): ?array
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
    public function testConnection(): array
    {
        try {
            // Use the /tax endpoint to test credentials
            // Note: This endpoint returns "Firma este neplatitoare de tva" for non-VAT payers,
            // which is actually a successful response (credentials are valid)
            $url = $this->baseUrl . '/tax?cif=' . urlencode($this->cif);

            $response = Http::withHeaders([
                'Authorization' => $this->getAuthHeader(),
                'Accept' => 'application/json',
            ])->timeout(15)->get($url);

            $responseData = $response->json();

            // Check for authentication errors (401)
            if ($response->status() === 401) {
                throw new Exception('Autentificare eșuată');
            }

            // Check for company not found in cloud
            if (isset($responseData['errorText']) &&
                str_contains($responseData['errorText'], 'nu mai este disponibila in Cloud')) {
                throw new Exception($responseData['errorText']);
            }

            // "Firma este neplatitoare de tva" is actually a successful response
            // It means the API connected successfully and the company exists
            if ($response->status() === 400 &&
                isset($responseData['errorText']) &&
                str_contains($responseData['errorText'], 'neplatitoare de tva')) {
                return [
                    'success' => true,
                    'message' => 'Conectare reușită la Smartbill API! Credențialele sunt valide. (Firma este neplătitoare de TVA)',
                ];
            }

            // Any successful response means credentials are valid
            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Conectare reușită la Smartbill API! Credențialele sunt valide.',
                ];
            }

            throw new Exception('Eroare necunoscută: HTTP ' . $response->status());

        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            // Translate common Smartbill error messages to user-friendly Romanian
            if (str_contains($errorMessage, 'Autentificare esuata') || str_contains($errorMessage, 'authentication failed')) {
                $errorMessage = 'Autentificare eșuată. Verificați numele de utilizator și token-ul API. ' .
                    'Asigurați-vă că folosiți email-ul de logare și token-ul API (nu parola contului). ' .
                    'Token-ul API îl găsiți în Smartbill: Setări → Integrări → API.';
            } elseif (str_contains($errorMessage, 'nu mai este disponibila in Cloud') || str_contains($errorMessage, 'not available')) {
                $errorMessage = 'CIF-ul ' . $this->cif . ' nu este disponibil în Smartbill Cloud. ' .
                    'Verificați: 1) CIF-ul este corect, 2) Aveți un abonament activ Smartbill Cloud, ' .
                    '3) Accesul API este activat în setările Smartbill.';
            } elseif (str_contains($errorMessage, 'Unauthorized') || str_contains($errorMessage, '401')) {
                $errorMessage = 'Acces neautorizat. Verificați credențialele în contul Smartbill: ' .
                    'https://cloud.smartbill.ro/core/integrari/';
            } elseif (str_contains($errorMessage, 'Not Found') || str_contains($errorMessage, '404')) {
                $errorMessage = 'Endpoint API negăsit. API-ul Smartbill poate fi indisponibil temporar.';
            } elseif (str_contains($errorMessage, 'Connection') || str_contains($errorMessage, 'timeout')) {
                $errorMessage = 'Nu s-a putut conecta la serverul Smartbill. Verificați conexiunea la internet.';
            }

            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        }
    }
}
