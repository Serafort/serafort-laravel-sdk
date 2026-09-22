<?php

declare(strict_types=1);

namespace Serafort\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RequirePermission
{
    /**
     * Handle an incoming request.
     *
     * @param string ...$permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user || !method_exists($user, 'hasPermission')) {
            throw new HttpException(401, 'Unauthenticated.');
        }

        foreach ($permissions as $permission) {
            if (!$user->hasPermission($permission)) {
                throw new HttpException(403, "Access denied: missing required permission '{$permission}'.");
            }
        }

        return $next($request);
    }
}
