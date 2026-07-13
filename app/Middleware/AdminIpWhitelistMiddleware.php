<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Http\Response;
use Core\Logger\Logger;

/**
 * AdminIpWhitelistMiddleware — restricts admin panel access to known IP addresses.
 * Set ADMIN_ALLOWED_IPS=1.2.3.4,5.6.7.8 in .env to enable.
 * Leave empty to disable (all IPs allowed).
 */
class AdminIpWhitelistMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        $allowedRaw = $_ENV['ADMIN_ALLOWED_IPS'] ?? '';

        if (empty(trim($allowedRaw))) {
            return $next($request);
        }

        $allowed = array_map('trim', explode(',', $allowedRaw));
        $ip      = $request->ip();

        if (!in_array($ip, $allowed, true)) {
            Logger::getInstance()->warning('Admin panel access blocked — IP not whitelisted.', ['ip' => $ip]);
            return Response::make('', 403);
        }

        return $next($request);
    }
}
