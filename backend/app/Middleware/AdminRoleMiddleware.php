<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Http\Response;
use App\Enums\AdminRole;

/**
 * AdminRoleMiddleware — enforces role-based access within the admin panel.
 * Usage in routes: admin.role:super_admin  |  admin.role:admin  |  admin.role:moderator
 *
 * Must run after AdminAuthMiddleware (auth_admin must be set).
 */
class AdminRoleMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        /** @var \App\Models\Admin|null $admin */
        $admin = $request->getAttribute('auth_admin');

        if ($admin === null) {
            return Response::redirect('/admin/login');
        }

        // Default minimum: any authenticated admin passes
        if ($param === null) {
            return $next($request);
        }

        try {
            $required = AdminRole::from($param);
        } catch (\ValueError) {
            throw new \InvalidArgumentException("Unknown role slug in middleware param: $param");
        }

        if (!$admin->hasRole($required)) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::error('Insufficient permissions.', 403);
            }
            \Core\Session\Session::flash('error', 'You do not have permission to perform this action.');
            return Response::redirect('/admin/dashboard', 302);
        }

        return $next($request);
    }
}
