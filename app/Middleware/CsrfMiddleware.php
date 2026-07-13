<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Http\Response;
use Core\Security\CsrfGuard;

/**
 * CsrfMiddleware — validates _csrf_token on every state-mutating request.
 * Accepts the token from POST body or X-CSRF-Token header (for AJAX).
 */
class CsrfMiddleware
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)) {
            return $next($request);
        }

        $supplied = $request->input('_csrf_token')
            ?? $request->header('X-CSRF-Token')
            ?? '';

        if (!CsrfGuard::validate((string) $supplied)) {
            // CSRF failure — redirect back with error for web, JSON 403 for AJAX
            if ($request->isAjax() || $request->isJson()) {
                return Response::error('CSRF token mismatch.', 403);
            }

            \Core\Session\Session::flash('error', 'Your session has expired. Please try again.');
            return Response::redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        // Regenerate after each valid mutating request
        CsrfGuard::regenerate();

        return $next($request);
    }
}
