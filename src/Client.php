<?php

declare(strict_types=1);

namespace Serafort\Laravel;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class Client
{
    private HttpClient $http;

    public function __construct(
        public readonly string $endpoint,
        public readonly ?string $clientId = null,
        public readonly ?string $clientSecret = null,
        public readonly int $jwksCacheTtl = 86400,
        public readonly int $leeway = 60,
        ?HttpClient $httpClient = null
    ) {
        $this->http = $httpClient ?? new HttpClient([
            'base_uri' => rtrim($this->endpoint, '/') . '/',
            'timeout' => 10.0,
        ]);
        JWT::$leeway = $this->leeway;
    }

    /**
     * Validates a JWT access token using the cached JWKS public keys.
     */
    public function validateToken(string $token): UserContext
    {
        $keys = $this->getJwksKeys();
        try {
            $decoded = (array) JWT::decode($token, $keys);
            return UserContext::fromJwtPayload($decoded);
        } catch (\Throwable $e) {
            throw new RuntimeException("Token validation failed: {$e->getMessage()}", previous: $e);
        }
    }

    /**
     * Retrieves an M2M client_credentials token from Serafort with automatic proactive caching.
     */
    public function getAccessToken(string $scope = ''): string
    {
        if (!$this->clientId || !$this->clientSecret) {
            throw new RuntimeException('Client ID and Client Secret must be configured for M2M token generation.');
        }

        $cacheKey = "serafort:m2m:token:" . md5("{$this->clientId}:{$scope}");

        return Cache::remember($cacheKey, 3300, function () use ($scope) {
            $response = $this->http->post('oauth/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => $scope,
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);
            if (!isset($data['access_token'])) {
                throw new RuntimeException('Invalid token response from Serafort IAM.');
            }

            return (string) $data['access_token'];
        });
    }

    /**
     * Retrieves and caches the JWKS public keys from the Serafort cluster.
     * @return array<string, Key>
     */
    public function getJwksKeys(): array
    {
        $cacheKey = 'serafort:jwks:keys:' . md5($this->endpoint);

        $jwksData = Cache::remember($cacheKey, $this->jwksCacheTtl, function () {
            $response = $this->http->get('.well-known/jwks.json');
            return json_decode((string) $response->getBody(), true);
        });

        if (!is_array($jwksData) || !isset($jwksData['keys'])) {
            throw new RuntimeException('Invalid JWKS payload received from Serafort endpoint.');
        }

        return JWK::parseKeySet($jwksData);
    }
}
