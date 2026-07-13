<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;

/**
 * TrimStringsMiddleware — trims whitespace from all string inputs globally.
 */
class TrimStringsMiddleware
{
    private const NEVER_TRIM = ['password', 'password_confirmation', 'current_password'];

    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        array_walk_recursive($_POST, function (&$value, string $key): void {
            if (is_string($value) && !in_array($key, self::NEVER_TRIM, true)) {
                $value = trim($value);
            }
        });

        array_walk_recursive($_GET, function (&$value): void {
            if (is_string($value)) {
                $value = trim($value);
            }
        });

        return $next($request);
    }
}
