<?php

declare(strict_types=1);

namespace Serafort\Laravel;

use Illuminate\Contracts\Auth\Authenticatable;

class UserContext implements Authenticatable
{
    /**
     * @param string $userId
     * @param string $tenantId
     * @param array<string> $roles
     * @param array<string> $permissions
     * @param array<string, mixed> $claims
     */
    public function __construct(
        public readonly string $userId,
        public readonly string $tenantId,
        public readonly array $roles = [],
        public readonly array $permissions = [],
        public readonly array $claims = []
    ) {}

    public static function fromJwtPayload(array $payload): self
    {
        return new self(
            userId: $payload['sub'] ?? $payload['user_id'] ?? '',
            tenantId: $payload['tenant_id'] ?? '',
            roles: $payload['roles'] ?? [],
            permissions: $payload['permissions'] ?? [],
            claims: $payload
        );
    }

    /**
     * Checks if the user possesses the required permission (supports wildcards e.g. 'org:*').
     */
    public function hasPermission(string $required): bool
    {
        foreach ($this->permissions as $perm) {
            if ($perm === '*' || $perm === $required) {
                return true;
            }
            if (str_ends_with($perm, ':*')) {
                $prefix = substr($perm, 0, -2);
                if (str_starts_with($required, $prefix . ':') || $required === $prefix) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Checks if the user possesses the required role.
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    /**
     * Checks if the user belongs to the specified tenant ID.
     */
    public function hasTenant(string $tenantId): bool
    {
        return $this->tenantId === $tenantId;
    }

    // --- Authenticatable Interface Implementation ---

    public function getAuthIdentifierName(): string
    {
        return 'userId';
    }

    public function getAuthIdentifier(): string
    {
        return $this->userId;
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getAuthPasswordName(): string
    {
        return '';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void {}

    public function getRememberTokenName(): ?string
    {
        return null;
    }
}
