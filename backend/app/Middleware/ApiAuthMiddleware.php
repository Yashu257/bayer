<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Http\Response;
use App\Repositories\SessionRepository;
use App\Repositories\UserRepository;

/**
 * ApiAuthMiddleware — validates Authorization: Bearer <token> for user API routes.
 * Stateless: no PHP session involved.
 */
class ApiAuthMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return Response::error('Authentication token required.', 401);
        }

        $sessionRepo = new SessionRepository();
        $session     = $sessionRepo->findUserSession($token);

        if ($session === null) {
            return Response::error('Token is invalid or expired.', 401);
        }

        $userRepo = new UserRepository();
        $user     = $userRepo->findById((int) $session->user_id);

        if ($user === null || !$user->canLogin()) {
            return Response::error('Account unavailable.', 403);
        }

        // Slide token window
        $authConfig = require BASE_PATH . '/config/auth.php';
        $sessionRepo->touchUserSession($token, $authConfig['user']['session_lifetime']);

        $request->setAttribute('auth_user', $user);
        $request->setAttribute('api_token', $token);

        return $next($request);
    }
}
