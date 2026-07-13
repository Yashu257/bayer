<?php

declare(strict_types=1);

// =============================================================================
// Global helper functions — loaded by bootstrap.php before config files.
// =============================================================================

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    /** Quick read of a config value: config('mail.driver') */
    function config(string $key, mixed $default = null): mixed
    {
        static $configs = [];
        [$file, $item] = array_pad(explode('.', $key, 2), 2, null);
        if (!isset($configs[$file])) {
            $path = BASE_PATH . '/config/' . $file . '.php';
            $configs[$file] = file_exists($path) ? require $path : [];
        }
        if ($item === null) {
            return $configs[$file];
        }
        return $configs[$file][$item] ?? $default;
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return BASE_PATH . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
    }
}

if (!function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        return APP_PATH . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        foreach ($vars as $v) {
            echo '<pre>' . htmlspecialchars(print_r($v, true)) . '</pre>';
        }
        exit(1);
    }
}

if (!function_exists('e')) {
    /** HTML-encode a string for safe output. */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
