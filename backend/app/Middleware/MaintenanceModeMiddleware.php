<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Http\Response;

/**
 * MaintenanceModeMiddleware — returns 503 when APP_MAINTENANCE=true in .env.
 * Bypass with ?bypass=APP_MAINTENANCE_KEY query param.
 */
class MaintenanceModeMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        $isMaintenance = filter_var($_ENV['APP_MAINTENANCE'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$isMaintenance) {
            return $next($request);
        }

        $bypassKey      = $_ENV['APP_MAINTENANCE_KEY'] ?? null;
        $suppliedBypass = $request->query('bypass');

        if ($bypassKey && $suppliedBypass === $bypassKey) {
            return $next($request);
        }

        if ($request->isJson() || $request->isAjax()) {
            return Response::error('Service temporarily unavailable.', 503);
        }

        return Response::redirect('/maintenance')->withStatus(503);
    }
}
