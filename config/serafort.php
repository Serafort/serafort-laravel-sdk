<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Serafort Endpoint
    |--------------------------------------------------------------------------
    | The base URL of your Serafort IAM cluster (e.g. https://auth.serafort.com).
    */
    'endpoint' => env('SERAFORT_ENDPOINT', 'https://api.serafort.com'),

    /*
    |--------------------------------------------------------------------------
    | Client Credentials (for M2M communication)
    |--------------------------------------------------------------------------
    */
    'client_id' => env('SERAFORT_CLIENT_ID'),
    'client_secret' => env('SERAFORT_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | JWKS Public Key Cache TTL (seconds)
    |--------------------------------------------------------------------------
    | How long to cache public keys from the Serafort JWKS endpoint locally.
    */
    'jwks_cache_ttl' => (int) env('SERAFORT_JWKS_CACHE_TTL', 86400),

    /*
    |--------------------------------------------------------------------------
    | Clock Skew Tolerance (seconds)
    |--------------------------------------------------------------------------
    */
    'leeway' => (int) env('SERAFORT_CLOCK_LEEWAY', 60),

    /*
    |--------------------------------------------------------------------------
    | Custom Header for Tenant Propagation
    |--------------------------------------------------------------------------
    */
    'tenant_header' => env('SERAFORT_TENANT_HEADER', 'X-Tenant-ID'),
];
