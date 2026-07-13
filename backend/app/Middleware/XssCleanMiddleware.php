<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Security\Sanitizer;

/**
 * XssCleanMiddleware — strips tags from all string inputs before they reach controllers.
 * Sanitisation here; HTML-escaping happens at view render time via Sanitizer::e().
 */
class XssCleanMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        // Mutate superglobals — the Request object reads from them
        $_POST   = Sanitizer::cleanArray($_POST);
        $_GET    = Sanitizer::cleanArray($_GET);

        return $next($request);
    }
}
