<?php

declare(strict_types=1);

namespace Serafort\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Serafort\Laravel\Client;

/**
 * @method static \Serafort\Laravel\UserContext validateToken(string $token)
 * @method static string getAccessToken(string $scope = '')
 * @method static array getJwksKeys()
 *
 * @see \Serafort\Laravel\Client
 */
class Serafort extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
