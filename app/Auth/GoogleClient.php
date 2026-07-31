<?php
namespace App\Auth;

use GuzzleHttp\Client;
use RuntimeException;

class GoogleClient {
    // =========================================================================
    // CONSTANTS
    // =========================================================================
    private const GOOGLE_AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const GOOGLE_USERINFO_URL = 'https://www.googleapis.com/oauth2/v3/userinfo';
    private const DEFAULT_SCOPE = 'openid email profile';
    private const HTTP_TIMEOUT = 10;

    // =========================================================================
    // PROPERTIES
    // =========================================================================
    private Client $http;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    // =========================================================================
    // CONSTRUCTOR
    // =========================================================================
    public function __construct() {
        $this->http = new Client([
            'timeout' => self::HTTP_TIMEOUT,
            'http_errors' => false,
        ]);

        $this->clientId = $_ENV['GOOGLE_CLIENT_ID'];
        $this->clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];
        $this->redirectUri = $_ENV['GOOGLE_REDIRECT_URI'];
    }

    // =========================================================================
    // PUBLIC API - OAuth Flow
    // =========================================================================
    
    /**
     * Generate Google's OAuth authorization URL.
     */
    public function getAuthorizationUrl(string $state): string {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => self::DEFAULT_SCOPE,
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'select_account',
        ];

        return self::GOOGLE_AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token.
     */
    public function exchangeCode(string $code): array {
        $response = $this->http->post(self::GOOGLE_TOKEN_URL, [
            'form_params' => [
                'code' => $code,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri,
                'grant_type' => 'authorization_code',
            ],
        ]);

        $token = $this->decodeResponse($response);

        if (empty($token['access_token'])) {
            throw new RuntimeException(
                $token['error_description'] ?? 'Unable to obtain Google access token.'
            );
        }

        return $token;
    }

    /**
     * Retrieve the authenticated Google user's profile.
     */
    public function getUser(string $accessToken): array {
        $response = $this->http->get(self::GOOGLE_USERINFO_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        if ($response->getStatusCode() >= 300) {
            throw new RuntimeException('Unable to retrieve Google profile.');
        }

        $user = $this->decodeResponse($response);

        if (!empty($user['error'])) {
            throw new RuntimeException('Unable to retrieve Google profile.');
        }

        return $user;
    }

    // =========================================================================
    // PRIVATE - Helpers
    // =========================================================================
    
    /**
     * Decode JSON response with consistent error handling.
     */
    private function decodeResponse($response): array {
        return json_decode(
            $response->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}