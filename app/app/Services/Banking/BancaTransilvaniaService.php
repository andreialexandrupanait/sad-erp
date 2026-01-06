<?php

namespace App\Services\Banking;

use App\Models\BankingCredential;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BancaTransilvaniaService
{
    protected string $baseUrl;
    protected int $timeout;
    protected int $retryAttempts;
    protected int $retryDelay;

    public function __construct()
    {
        $this->baseUrl = config('banking.banca_transilvania.sandbox_mode')
            ? config('banking.banca_transilvania.sandbox_url')
            : config('banking.banca_transilvania.production_url');

        $this->timeout = config('banking.banca_transilvania.timeout', 30);
        $this->retryAttempts = config('banking.banca_transilvania.retry_attempts', 3);
        $this->retryDelay = config('banking.banca_transilvania.retry_delay', 1000);
    }

    /**
     * Generate PKCE code verifier and challenge
     */
    public function generatePKCE(): array
    {
        // Generate code verifier (43-128 characters, URL-safe)
        $codeVerifier = Str::random(128);

        // Generate code challenge (base64url encoded SHA256 hash)
        $codeChallenge = rtrim(
            strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'),
            '='
        );

        return [
            'code_verifier' => $codeVerifier,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ];
    }

    /**
     * Generate OAuth2 authorization URL
     */
    public function getAuthorizationUrl(array $pkce, ?string $state = null): string
    {
        $params = [
            'response_type' => 'code',
            'client_id' => config('banking.banca_transilvania.client_id'),
            'redirect_uri' => config('banking.banca_transilvania.redirect_uri'),
            'scope' => implode(' ', config('banking.banca_transilvania.scopes')),
            'state' => $state ?? Str::random(32), // CSRF protection - use provided state or generate one
            'code_challenge' => $pkce['code_challenge'],
            'code_challenge_method' => $pkce['code_challenge_method'],
        ];

        // Use dedicated OAuth authorize URL or fallback to base URL
        $oauthUrl = config('banking.banca_transilvania.oauth_authorize_url')
            ?? $this->baseUrl . '/oauth/authorize';

        $authUrl = $oauthUrl . '?' . http_build_query($params);

        Log::info('BT Authorization URL generated', [
            'url' => $authUrl,
            'base_url' => $this->baseUrl,
            'oauth_url' => $oauthUrl,
            'client_id' => config('banking.banca_transilvania.client_id'),
            'redirect_uri' => config('banking.banca_transilvania.redirect_uri'),
        ]);

        return $authUrl;
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeAuthorizationCode(string $code, string $codeVerifier): array
    {
        try {
            // Use dedicated OAuth token URL or fallback to base URL
            $tokenUrl = config('banking.banca_transilvania.oauth_token_url')
                ?? $this->baseUrl . '/oauth/token';

            $response = Http::timeout($this->timeout)
                ->asForm()
                ->post($tokenUrl, [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => config('banking.banca_transilvania.redirect_uri'),
                    'client_id' => config('banking.banca_transilvania.client_id'),
                    'client_secret' => config('banking.banca_transilvania.client_secret'),
                    'code_verifier' => $codeVerifier,
                ]);

            if (!$response->successful()) {
                Log::error('BT token exchange failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Token exchange failed: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('BT OAuth token exchange exception', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken(BankingCredential $credential): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->asForm()
                ->post($this->baseUrl . '/oauth2/token', [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $credential->refresh_token,
                    'client_id' => config('banking.banca_transilvania.client_id'),
                    'client_secret' => config('banking.banca_transilvania.client_secret'),
                ]);

            if (!$response->successful()) {
                throw new \Exception('Token refresh failed: ' . $response->body());
            }

            $data = $response->json();

            // Update credential with new tokens
            $credential->updateTokens(
                $data['access_token'],
                $data['refresh_token'] ?? $credential->refresh_token,
                $data['expires_in'] ?? 3600
            );

            return $data;
        } catch (\Exception $e) {
            Log::error('BT token refresh failed', [
                'credential_id' => $credential->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Refresh token with database locking to prevent race conditions
     *
     * When multiple concurrent requests detect an expired token, this method ensures
     * only one actually performs the refresh while others wait and get the new token.
     */
    protected function refreshTokenWithLock(BankingCredential $credential): void
    {
        DB::transaction(function () use ($credential) {
            // Lock the credential row for update
            $lockedCredential = BankingCredential::lockForUpdate()->find($credential->id);

            if (!$lockedCredential) {
                throw new \Exception('Banking credential not found');
            }

            // Double-check if token is still expired after acquiring lock
            // Another process might have already refreshed it
            if ($lockedCredential->isTokenExpired()) {
                $this->refreshAccessToken($lockedCredential);
            }

            // Reload the credential with new token data
            $credential->refresh();
        });
    }

    /**
     * Make authenticated API request
     */
    protected function makeRequest(string $method, string $endpoint, BankingCredential $credential, array $params = []): array
    {
        // Refresh token if expired - use locking to prevent race conditions
        if ($credential->isTokenExpired()) {
            $this->refreshTokenWithLock($credential);
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($credential->access_token)
                ->retry($this->retryAttempts, $this->retryDelay)
                ->{strtolower($method)}($this->baseUrl . $endpoint, $params);

            if (!$response->successful()) {
                throw new \Exception('API request failed: ' . $response->body());
            }

            if (config('banking.logging.log_api_requests')) {
                Log::info('BT API request', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                ]);
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('BT API request failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get list of accounts
     */
    public function getAccounts(BankingCredential $credential): array
    {
        return $this->makeRequest('GET', '/psd2/v1/accounts', $credential);
    }

    /**
     * Get account details by ID
     */
    public function getAccount(BankingCredential $credential, string $accountId): array
    {
        return $this->makeRequest('GET', "/psd2/v1/accounts/{$accountId}", $credential);
    }

    /**
     * Get account balances
     */
    public function getBalances(BankingCredential $credential, string $accountId): array
    {
        return $this->makeRequest('GET', "/psd2/v1/accounts/{$accountId}/balances", $credential);
    }

    /**
     * Get transactions for an account
     */
    public function getTransactions(
        BankingCredential $credential,
        string $accountId,
        string $dateFrom,
        string $dateTo,
        ?string $continuationKey = null
    ): array {
        $params = [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];

        if ($continuationKey) {
            $params['continuationKey'] = $continuationKey;
        }

        $endpoint = "/psd2/v1/accounts/{$accountId}/transactions?" . http_build_query($params);

        return $this->makeRequest('GET', $endpoint, $credential);
    }

    /**
     * Get transaction details by ID
     */
    public function getTransaction(
        BankingCredential $credential,
        string $accountId,
        string $transactionId
    ): array {
        return $this->makeRequest(
            'GET',
            "/psd2/v1/accounts/{$accountId}/transactions/{$transactionId}",
            $credential
        );
    }

    /**
     * Download account statement (PDF)
     */
    public function downloadStatement(
        BankingCredential $credential,
        string $accountId,
        string $dateFrom,
        string $dateTo
    ): string {
        // Refresh token if expired - use locking to prevent race conditions
        if ($credential->isTokenExpired()) {
            $this->refreshTokenWithLock($credential);
        }

        try {
            $params = [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'format' => 'pdf',
            ];

            $endpoint = "/psd2/v1/accounts/{$accountId}/statement?" . http_build_query($params);

            $response = Http::timeout($this->timeout)
                ->withToken($credential->access_token)
                ->get($this->baseUrl . $endpoint);

            if (!$response->successful()) {
                throw new \Exception('Statement download failed: ' . $response->body());
            }

            return $response->body();
        } catch (\Exception $e) {
            Log::error('BT statement download failed', [
                'account_id' => $accountId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create PSD2 consent
     */
    public function createConsent(BankingCredential $credential, array $accountIds): array
    {
        $params = [
            'access' => [
                'accounts' => array_map(fn($id) => ['iban' => $id], $accountIds),
                'balances' => array_map(fn($id) => ['iban' => $id], $accountIds),
                'transactions' => array_map(fn($id) => ['iban' => $id], $accountIds),
            ],
            'recurringIndicator' => true,
            'validUntil' => now()->addDays(config('banking.banca_transilvania.consent.validity_days', 90))->format('Y-m-d'),
            'frequencyPerDay' => 4,
        ];

        return $this->makeRequest('POST', '/psd2/v1/consents', $credential, $params);
    }

    /**
     * Get consent status
     */
    public function getConsentStatus(BankingCredential $credential, string $consentId): array
    {
        return $this->makeRequest('GET', "/psd2/v1/consents/{$consentId}/status", $credential);
    }

    /**
     * Delete consent
     */
    public function deleteConsent(BankingCredential $credential, string $consentId): void
    {
        $this->makeRequest('DELETE', "/psd2/v1/consents/{$consentId}", $credential);
    }

    /**
     * Test API connection
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl . '/health');
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('BT connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
