<?php

declare(strict_types=1);

namespace Core\Security;

use Core\Session\Session;

/**
 * CsrfGuard — generates and validates CSRF tokens stored in session.
 */
final class CsrfGuard
{
    private const SESSION_KEY = '_csrf_token';
    private const TOKEN_BYTES = 32;

    public static function token(): string
    {
        if (!Session::has(self::SESSION_KEY)) {
            Session::set(self::SESSION_KEY, bin2hex(random_bytes(self::TOKEN_BYTES)));
        }
        return Session::get(self::SESSION_KEY);
    }

    public static function validate(string $supplied): bool
    {
        $stored = Session::get(self::SESSION_KEY, '');
        return hash_equals($stored, $supplied);
    }

    /** Regenerate the token after a successful state-mutating request. */
    public static function regenerate(): void
    {
        Session::set(self::SESSION_KEY, bin2hex(random_bytes(self::TOKEN_BYTES)));
    }
}
