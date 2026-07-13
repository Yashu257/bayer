<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Http\Response;

/**
 * EmailVerifiedMiddleware — blocks access until the user has verified their email.
 * Must run after AuthMiddleware (relies on auth_user being set).
 */
class EmailVerifiedMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        /** @var \App\Models\User|null $user */
        $user = $request->getAttribute('auth_user');

        if ($user === null || !$user->isVerified()) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::error('Email address not verified.', 403);
            }
            return Response::redirect('/verify-email/notice');
        }

        return $next($request);
    }
}
