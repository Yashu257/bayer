<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use Core\Http\Request;
use Core\Http\Response;

class SystemController
{
    public function health(Request $request): Response
    {
        return Response::json(['status' => 'ok', 'timestamp' => time()]);
    }

    public function version(Request $request): Response
    {
        return Response::json([
            'version' => $_ENV['APP_VERSION'] ?? '1.0.0',
            'env'     => $_ENV['APP_ENV']     ?? 'production',
        ]);
    }
}
