<?php

declare(strict_types=1);

namespace Core\Session;

/**
 * Session — secure session facade.
 * Wraps PHP native sessions with hardened configuration.
 */
final class Session
{
    private static bool $started = false;

    private function __construct() {}

    public static function start(array $config): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Harden session cookie parameters before any output
        session_set_cookie_params([
            'lifetime' => $config['lifetime'],
            'path'     => $config['path'],
            'domain'   => $config['domain'],
            'secure'   => $config['secure'],
            'httponly' => $config['http_only'],
            'samesite' => $config['same_site'],
        ]);

        if (!empty($config['save_path']) && is_dir($config['save_path'])) {
            session_save_path($config['save_path']);
        }

        ini_set('session.gc_maxlifetime', (string) $config['gc_maxlifetime']);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');

        session_name($config['name'] ?? 'PHARMA_SID');
        session_start();

        self::$started = true;

        // Regenerate ID on first request (session fixation prevention)
        if (!self::has('_session_initialised')) {
            session_regenerate_id(true);
            self::set('_session_initialised', true);
        }
    }

    // --- Accessors -----------------------------------------------------------

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function all(): array
    {
        return $_SESSION;
    }

    // --- Flash messages ------------------------------------------------------

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    // --- Old input (re-populate forms after redirect) ------------------------

    public static function flashOld(array $input): void
    {
        self::flash('_old_input', $input);
    }

    public static function old(string $key, mixed $default = null): mixed
    {
        $old = $_SESSION['_flash']['_old_input'] ?? [];
        return $old[$key] ?? $default;
    }

    // --- Lifecycle -----------------------------------------------------------

    public static function regenerate(bool $deleteOld = true): void
    {
        session_regenerate_id($deleteOld);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        session_unset();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        self::$started = false;
    }
}
