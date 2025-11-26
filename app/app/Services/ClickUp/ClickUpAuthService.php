<?php

namespace App\Services\ClickUp;

use Illuminate\Support\Facades\Http;
use Exception;

class ClickUpAuthService
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;

    public function __construct()
    {
        $this->clientId = config('services.clickup.client_id');
        $this->clientSecret = config('services.clickup.client_secret');
        $this->redirectUri = config('services.clickup.redirect_uri');
    }

    /**
     * Get the OAuth authorization URL
     *
     * @return string
     */
    public function getAuthorizationUrl()
    {
        $params = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
        ]);

        return "https://app.clickup.com/api?{$params}";
    }

    /**
     * Exchange authorization code for access token
     *
     * @param string $code Authorization code from ClickUp
     * @return array Token data including access_token
     * @throws Exception
     */
    public function exchangeCodeForToken($code)
    {
        $response = Http::asForm()->post('https://api.clickup.com/api/v2/oauth/token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
        ]);

        if ($response->failed()) {
            throw new Exception('Failed to exchange code for token: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Get a ClickUp client instance using personal token
     *
     * @param string|null $token Optional token (defaults to config)
     * @return ClickUpClient
     */
    public function getClientWithPersonalToken($token = null)
    {
        $token = $token ?? config('services.clickup.personal_token');

        if (!$token) {
            throw new Exception('ClickUp personal token not configured');
        }

        return new ClickUpClient($token);
    }

    /**
     * Get a ClickUp client instance using OAuth access token
     *
     * @param string $accessToken OAuth access token
     * @return ClickUpClient
     */
    public function getClientWithAccessToken($accessToken)
    {
        return new ClickUpClient("Bearer {$accessToken}");
    }
}
