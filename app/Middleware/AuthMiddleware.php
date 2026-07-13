<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Http\Response;
use App\Services\AuthService;

/**
 * AuthMiddleware — ensures a valid user session exists.
 * Also resolves remember-me cookies on first visit.
 */
class AuthMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        $authService = new AuthService();
        $authConfig  = require BASE_PATH . '/config/auth.php';

        // 1. Try PHP session first
        $user = $authService->resolveSession();

        // 2. Fall back to remember-me cookie
        if ($user === null) {
            $cookieName    = $authConfig['user']['cookie_name'];
            $rememberToken = $request->cookie($cookieName);

            if ($rememberToken) {
                $user = $authService->resolveRememberToken($rememberToken);
                if ($user !== null) {
                    // Promote to full session
                    \Core\Session\Session::set($authConfig['user']['session_key'], [
                        'id'    => $user->id,
                        'email' => $user->email,
                        'token' => $rememberToken,
                    ]);
                }
            }
        }

        if ($user === null) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::error('Unauthenticated.', 401);
            }
            \Core\Session\Session::flash('intended', $request->uri());
            return Response::redirect('/login');
        }

        // Bind the resolved user to the request for downstream use
        $request->setAttribute('auth_user', $user);

        return $next($request);
    }
}
