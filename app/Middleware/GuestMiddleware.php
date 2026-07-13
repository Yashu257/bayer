<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Http\Response;
use App\Services\AuthService;

/**
 * GuestMiddleware — redirects authenticated users away from guest-only pages
 * (login form, register form).
 */
class GuestMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        $authService = new AuthService();

        if ($authService->resolveSession() !== null) {
            return Response::redirect('/profile');
        }

        return $next($request);
    }
}
