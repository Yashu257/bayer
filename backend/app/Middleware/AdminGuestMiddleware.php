<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Http\Response;
use App\Services\AdminAuthService;

class AdminGuestMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        if ((new AdminAuthService())->resolveSession() !== null) {
            return Response::redirect('/admin/dashboard');
        }
        return $next($request);
    }
}
