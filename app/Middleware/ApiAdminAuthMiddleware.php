<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Http\Response;
use App\Repositories\SessionRepository;
use App\Repositories\AdminRepository;

/**
 * ApiAdminAuthMiddleware — validates Bearer token for admin API routes.
 */
class ApiAdminAuthMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return Response::error('Admin authentication required.', 401);
        }

        $sessionRepo = new SessionRepository();
        $session     = $sessionRepo->findAdminSession($token);

        if ($session === null) {
            return Response::error('Token is invalid or expired.', 401);
        }

        $adminRepo = new AdminRepository();
        $admin     = $adminRepo->findById((int) $session->admin_id);

        if ($admin === null || !$admin->isActive()) {
            return Response::error('Admin account unavailable.', 403);
        }

        $authConfig = require BASE_PATH . '/config/auth.php';
        $sessionRepo->touchAdminSession($token, $authConfig['admin']['session_lifetime']);

        $request->setAttribute('auth_admin', $admin);

        return $next($request);
    }
}
