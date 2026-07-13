<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Logger\Logger;

/**
 * RequestLoggingMiddleware — logs every inbound request at debug level.
 */
class RequestLoggingMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        $start    = microtime(true);
        $response = $next($request);
        $duration = round((microtime(true) - $start) * 1000, 2);

        Logger::getInstance()->debug('Request', [
            'method'   => $request->method(),
            'uri'      => $request->uri(),
            'ip'       => $request->ip(),
            'duration' => "{$duration}ms",
        ]);

        return $response;
    }
}
