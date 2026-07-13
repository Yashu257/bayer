<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;

/**
 * JsonResponseMiddleware — sets Content-Type header for all API group routes.
 */
class JsonResponseMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        header('Content-Type: application/json; charset=UTF-8');
        return $next($request);
    }
}
