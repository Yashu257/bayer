<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * TokenHelper — cryptographically secure random token generation.
 */
final class TokenHelper
{
    /** Generate a URL-safe hex token of $bytes bytes (resulting in $bytes*2 chars). */
    public static function generate(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    /** Generate a Bearer-style token stored in user_sessions / admin_sessions. */
    public static function generateApiToken(): string
    {
        $config = require BASE_PATH . '/config/auth.php';
        return bin2hex(random_bytes($config['token']['api']));
    }

    /** Generate a remember-me cookie token. */
    public static function generateRememberToken(): string
    {
        $config = require BASE_PATH . '/config/auth.php';
        return bin2hex(random_bytes($config['token']['remember']));
    }

    /** Generate an email verification token. */
    public static function generateVerificationToken(): string
    {
        $config = require BASE_PATH . '/config/auth.php';
        return bin2hex(random_bytes($config['token']['verification']));
    }

    /** Generate a password reset token. */
    public static function generateResetToken(): string
    {
        $config = require BASE_PATH . '/config/auth.php';
        return bin2hex(random_bytes($config['token']['reset']));
    }

    /** Timing-safe comparison (wrapper for hash_equals). */
    public static function compare(string $known, string $supplied): bool
    {
        return hash_equals($known, $supplied);
    }
}
