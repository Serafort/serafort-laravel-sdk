<?php

declare(strict_types=1);

namespace Serafort\Laravel\Tests;

use PHPUnit\Framework\TestCase;
use Serafort\Laravel\UserContext;

class UserContextTest extends TestCase
{
    public function testWildcardPermissions(): void
    {
        $user = new UserContext(
            userId: 'usr_php_123',
            tenantId: 'tenant_laravel',
            roles: ['admin', 'maintainer'],
            permissions: ['org:*', 'billing:read']
        );

        // Wildcard match
        $this->assertTrue($user->hasPermission('org:members:invite'));
        $this->assertTrue($user->hasPermission('org:settings:update'));

        // Exact match
        $this->assertTrue($user->hasPermission('billing:read'));

        // Non-match
        $this->assertFalse($user->hasPermission('billing:write'));
        $this->assertFalse($user->hasPermission('system:root'));

        // Global wildcard
        $super = new UserContext(
            userId: 'usr_super',
            tenantId: 'tenant_laravel',
            roles: ['superadmin'],
            permissions: ['*']
        );
        $this->assertTrue($super->hasPermission('any:permission'));
    }

    public function testRolesAndTenant(): void
    {
        $user = new UserContext(
            userId: 'usr_php_123',
            tenantId: 'tenant_laravel',
            roles: ['admin']
        );

        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('guest'));

        $this->assertTrue($user->hasTenant('tenant_laravel'));
        $this->assertFalse($user->hasTenant('other_tenant'));
    }

    public function testAuthenticatableIdentifier(): void
    {
        $user = new UserContext(
            userId: 'usr_php_123',
            tenantId: 'tenant_laravel'
        );

        $this->assertSame('userId', $user->getAuthIdentifierName());
        $this->assertSame('usr_php_123', $user->getAuthIdentifier());
    }
}
