<?php

declare(strict_types=1);

namespace Serafort\Laravel\Guards;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Serafort\Laravel\Client;
use Serafort\Laravel\UserContext;

class SerafortGuard implements Guard
{
    protected ?UserContext $user = null;

    public function __construct(
        protected Client $client,
        protected Request $request
    ) {}

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function user(): ?UserContext
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $token = $this->request->bearerToken();
        if (!$token) {
            return null;
        }

        try {
            $this->user = $this->client->validateToken($token);
            return $this->user;
        } catch (\Throwable) {
            return null;
        }
    }

    public function id(): ?string
    {
        return $this->user()?->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        $token = $credentials['token'] ?? null;
        if (!$token) {
            return false;
        }

        try {
            $this->client->validateToken((string) $token);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    public function setUser(Authenticatable $user): self
    {
        if ($user instanceof UserContext) {
            $this->user = $user;
        }
        return $this;
    }
}
