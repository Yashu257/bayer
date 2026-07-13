<?php

declare(strict_types=1);

// =============================================================================
// BOOTSTRAP — Loaded once per request via public/index.php
// =============================================================================

// --- Helpers (must come before config files that call env()) -----------------
require_once BASE_PATH . '/core/helpers.php';

// --- Environment -------------------------------------------------------------
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

// --- Error handling ----------------------------------------------------------
$isDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', $isDebug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php_error.log');

set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    // Ignore deprecation warnings
    if ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) {
        return true;
    }
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

set_exception_handler(function (\Throwable $e): void {
    \Core\Logger\Logger::getInstance()->error($e->getMessage(), [
        'file'  => $e->getFile(),
        'line'  => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);

    if (filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
        echo '<pre>' . htmlspecialchars((string) $e) . '</pre>';
    } else {
        http_response_code(500);
        include APP_PATH . '/Views/errors/500.php';
    }
    exit(1);
});

// --- Autoloader --------------------------------------------------------------
spl_autoload_register(function (string $class): void {
    $namespaceMap = [
        'Core\\' => CORE_PATH . '/',
        'App\\'  => APP_PATH  . '/',
    ];

    foreach ($namespaceMap as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }
        $relative = substr($class, strlen($prefix));
        $file     = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// --- Timezone ----------------------------------------------------------------
$appConfig = require BASE_PATH . '/config/app.php';
date_default_timezone_set($appConfig['timezone']);

// --- Session -----------------------------------------------------------------
\Core\Session\Session::start(require BASE_PATH . '/config/session.php');

// --- Router ------------------------------------------------------------------
$router = new \Core\Router\Router();

require BASE_PATH . '/routes/web.php';
require BASE_PATH . '/routes/admin.php';
require BASE_PATH . '/routes/api.php';

$middlewareMap = require BASE_PATH . '/routes/middleware.php';
$router->setMiddlewareMap($middlewareMap);
$router->dispatch(\Core\Http\Request::fromGlobals());
