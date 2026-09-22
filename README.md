# Serafort SDK for Laravel

Enterprise IAM, OAuth2/OIDC, Multi-Tenant Isolation, and Wildcard RBAC for Laravel 10 and 11.

## Features

- 🛡️ **Custom Auth Guard**: `Auth::guard('serafort')` parsing Bearer tokens and validating against local JWKS with caching.
- ⚡ **Wildcard RBAC Middleware**: `serafort.permission:org:*` and `serafort.role:admin`.
- 🏢 **Multi-Tenant Isolation**: Zero-leakage tenant separation on `UserContext`.
- 🔌 **Facade & Service Provider**: `Serafort::validateToken($token)` and `Serafort::getAccessToken()`.

## Installation

```bash
composer require serafort/laravel
```

Publish configuration:

```bash
php artisan vendor:publish --tag=serafort-config
```

## Quick Start

### 1. Configure `config/auth.php`

```php
'guards' => [
    'api' => [
        'driver' => 'serafort',
        'provider' => 'users',
    ],
],
```

### 2. Protect Routes in `routes/api.php`

```php
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'serafort.permission:org:*'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        return response()->json([
            'userId' => $user->userId,
            'tenantId' => $user->tenantId,
        ]);
    });
});
```
