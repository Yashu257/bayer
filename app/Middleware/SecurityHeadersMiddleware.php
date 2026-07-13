<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;

/**
 * SecurityHeadersMiddleware — injects security HTTP response headers on every request.
 * Runs as part of the global middleware stack.
 */
class SecurityHeadersMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        $response = $next($request);

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(self), microphone=(self), camera=(self)');
        header(
            "Content-Security-Policy: default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://player.mux.com; " .
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
            "img-src 'self' data: https: blob:; " .
            "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; " .
            "connect-src 'self' https:; " .
            "media-src 'self' https: blob:; " .
            "frame-src 'self' https://player.mux.com; " .
            "frame-ancestors 'none';"
        );

        return $response;
    }

    private function nonce(): string
    {
        // Store once per request so the same nonce can be used in views
        if (!\Core\Session\Session::has('_csp_nonce')) {
            \Core\Session\Session::set('_csp_nonce', base64_encode(random_bytes(16)));
        }
        return \Core\Session\Session::get('_csp_nonce');
    }
}
