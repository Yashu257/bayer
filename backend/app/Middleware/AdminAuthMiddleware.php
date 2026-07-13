<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Http\Response;
use App\Services\AdminAuthService;

/**
 * AdminAuthMiddleware — validates the admin panel session.
 * Completely separate from the user AuthMiddleware.
 */
class AdminAuthMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        $service = new AdminAuthService();
        $admin   = $service->resolveSession();

        if ($admin === null) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::error('Admin authentication required.', 401);
            }
            \Core\Session\Session::flash('intended', $request->uri());
            return Response::redirect('/admin/login');
        }

        $request->setAttribute('auth_admin', $admin);

        return $next($request);
    }
}
