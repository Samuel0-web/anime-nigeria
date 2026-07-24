<?php
namespace App\Auth;
use GuzzleHttp\Client;

class GoogleClient {
    private Client $http;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct() {
        $this->http = new Client([
            'timeout' => 10,
            'http_errors' => false,
        ]);

        $this->clientId = $_ENV['GOOGLE_CLIENT_ID'];
        $this->clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];
        $this->redirectUri = $_ENV['GOOGLE_REDIRECT_URI'];
    }

    /**
     * Generate Google's OAuth URL.
     */
    public function getAuthorizationUrl(string $state): string {
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'select_account',
        ]);
    }

    /**
     * Exchange authorization code for access token.
     */
    public function exchangeCode(string $code): array {
        $response = $this->http->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'code' => $code,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri,
                'grant_type' => 'authorization_code',
            ],
        ]);

        $token = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (empty($token['access_token']) || !empty($token['error'])) {
            throw new \RuntimeException($token['error_description']
                ?? 'Unable to obtain Google access token.'
            );
        }

        return $token;
    }

    /**
     * Retrieve the authenticated Google user's profile.
     */
    public function getUser(string $accessToken): array {
        $response = $this->http->get(
            'https://www.googleapis.com/oauth2/v3/userinfo',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ]
            ]
        );

        if ($response->getStatusCode() >= 300) {
            throw new \RuntimeException('Unable to retrieve Google profile.');
        }

        $user = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if (!empty($user['error'])) {
            throw new \RuntimeException('Unable to retrieve Google profile.');
        }

        return $user;
    }
}