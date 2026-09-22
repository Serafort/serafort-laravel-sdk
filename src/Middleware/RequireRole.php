<?php

declare(strict_types=1);

namespace Serafort\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RequireRole
{
    /**
     * Handle an incoming request.
     *
     * @param string ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !method_exists($user, 'hasRole')) {
            throw new HttpException(401, 'Unauthenticated.');
        }

        $hasAnyRole = false;
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $hasAnyRole = true;
                break;
            }
        }

        if (!$hasAnyRole) {
            $required = implode(', ', $roles);
            throw new HttpException(403, "Access denied: requires one of the following roles: {$required}.");
        }

        return $next($request);
    }
}
