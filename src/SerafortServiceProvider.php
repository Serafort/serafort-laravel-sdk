<?php

declare(strict_types=1);

namespace Serafort\Laravel;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Serafort\Laravel\Guards\SerafortGuard;
use Serafort\Laravel\Middleware\RequirePermission;
use Serafort\Laravel\Middleware\RequireRole;

class SerafortServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/serafort.php', 'serafort');

        $this->app->singleton(Client::class, function ($app) {
            $config = $app['config']->get('serafort');
            return new Client(
                endpoint: $config['endpoint'],
                clientId: $config['client_id'] ?? null,
                clientSecret: $config['client_secret'] ?? null,
                jwksCacheTtl: (int) ($config['jwks_cache_ttl'] ?? 86400),
                leeway: (int) ($config['leeway'] ?? 60)
            );
        });

        $this->app->alias(Client::class, 'serafort');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/serafort.php' => $this->app->configPath('serafort.php'),
            ], 'serafort-config');
        }

        // Register Custom Guard
        Auth::extend('serafort', function ($app, $name, array $config) {
            return new SerafortGuard(
                $app->make(Client::class),
                $app['request']
            );
        });

        // Register Route Middleware Aliases
        $router = $this->app['router'];
        $router->aliasMiddleware('serafort.permission', RequirePermission::class);
        $router->aliasMiddleware('serafort.role', RequireRole::class);
    }
}
