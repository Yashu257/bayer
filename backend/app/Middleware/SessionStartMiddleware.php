<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Session\Session;

/**
 * SessionStartMiddleware — ensures the PHP session is active for web/admin groups.
 * Session::start() is idempotent; safe to call more than once.
 */
class SessionStartMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        Session::start(require BASE_PATH . '/config/session.php');
        return $next($request);
    }
}
