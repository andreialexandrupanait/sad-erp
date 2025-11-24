<?php

namespace App\Services\ClickUp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ClickUpClient
{
    protected $baseUrl;
    protected $token;
    protected $rateLimiter;

    /**
     * Create a new ClickUp API client
     *
     * @param string $token Personal API token or Bearer token
     */
    public function __construct($token)
    {
        $this->baseUrl = config('services.clickup.base_url', 'https://api.clickup.com/api/v2');
        // If token doesn't start with 'Bearer ', assume it's a personal token and format it properly
        $this->token = str_starts_with($token, 'Bearer ') ? $token : $token;
        $this->rateLimiter = new ClickUpRateLimiter();
    }

    /**
     * Make a GET request to ClickUp API
     *
     * @param string $endpoint API endpoint (without base URL)
     * @param array $params Query parameters
     * @return array Response data
     * @throws Exception
     */
    public function get($endpoint, $params = [])
    {
        $url = $this->baseUrl . $endpoint;

        Log::debug('ClickUp API GET Request', [
            'url' => $url,
            'params' => $params,
        ]);

        $response = Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type' => 'application/json',
        ])
            ->timeout(30)
            ->get($url, $params);

        // Check rate limits
        $this->rateLimiter->checkHeaders($response);

        // Handle errors
        if ($response->failed()) {
            $this->handleError($response, 'GET', $url);
        }

        return $response->json();
    }

    /**
     * Make a POST request to ClickUp API
     *
     * @param string $endpoint API endpoint (without base URL)
     * @param array $data Request body data
     * @return array Response data
     * @throws Exception
     */
    public function post($endpoint, $data = [])
    {
        $url = $this->baseUrl . $endpoint;

        Log::debug('ClickUp API POST Request', [
            'url' => $url,
            'data' => $data,
        ]);

        $response = Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type' => 'application/json',
        ])
            ->timeout(30)
            ->post($url, $data);

        // Check rate limits
        $this->rateLimiter->checkHeaders($response);

        // Handle errors
        if ($response->failed()) {
            $this->handleError($response, 'POST', $url);
        }

        return $response->json();
    }

    /**
     * Make a PUT request to ClickUp API
     *
     * @param string $endpoint API endpoint (without base URL)
     * @param array $data Request body data
     * @return array Response data
     * @throws Exception
     */
    public function put($endpoint, $data = [])
    {
        $url = $this->baseUrl . $endpoint;

        Log::debug('ClickUp API PUT Request', [
            'url' => $url,
            'data' => $data,
        ]);

        $response = Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type' => 'application/json',
        ])
            ->timeout(30)
            ->put($url, $data);

        // Check rate limits
        $this->rateLimiter->checkHeaders($response);

        // Handle errors
        if ($response->failed()) {
            $this->handleError($response, 'PUT', $url);
        }

        return $response->json();
    }

    /**
     * Make a DELETE request to ClickUp API
     *
     * @param string $endpoint API endpoint (without base URL)
     * @return array Response data
     * @throws Exception
     */
    public function delete($endpoint)
    {
        $url = $this->baseUrl . $endpoint;

        Log::debug('ClickUp API DELETE Request', [
            'url' => $url,
        ]);

        $response = Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type' => 'application/json',
        ])
            ->timeout(30)
            ->delete($url);

        // Check rate limits
        $this->rateLimiter->checkHeaders($response);

        // Handle errors
        if ($response->failed()) {
            $this->handleError($response, 'DELETE', $url);
        }

        return $response->json();
    }

    /**
     * Handle API errors
     *
     * @param \Illuminate\Http\Client\Response $response
     * @param string $method HTTP method
     * @param string $url Request URL
     * @throws Exception
     */
    protected function handleError($response, $method, $url)
    {
        $status = $response->status();
        $body = $response->json();

        Log::error('ClickUp API Error', [
            'method' => $method,
            'url' => $url,
            'status' => $status,
            'body' => $body,
        ]);

        $errorMessage = $body['err'] ?? $body['error'] ?? 'Unknown ClickUp API error';

        throw new Exception("ClickUp API Error ({$status}): {$errorMessage}");
    }

    /**
     * Get the rate limiter instance
     *
     * @return ClickUpRateLimiter
     */
    public function getRateLimiter()
    {
        return $this->rateLimiter;
    }

    /**
     * Test the connection to ClickUp API
     *
     * @return array Authorized user information
     * @throws Exception
     */
    public function testConnection()
    {
        return $this->get('/user');
    }
}
