<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use Core\Http\Request;
use Core\Http\Response;

class ErrorController extends BaseController
{
    public function forbidden(Request $request): Response
    {
        http_response_code(403);
        return $this->view('frontend.errors.403', ['pageTitle' => '403 Forbidden'], 'frontend/layouts/main', 403);
    }

    public function notFound(Request $request): Response
    {
        http_response_code(404);
        return $this->view('frontend.errors.404', ['pageTitle' => '404 Not Found'], 'frontend/layouts/main', 404);
    }

    public function serverError(Request $request): Response
    {
        http_response_code(500);
        return $this->view('frontend.errors.500', ['pageTitle' => 'Server Error'], 'frontend/layouts/main', 500);
    }

    public function maintenance(Request $request): Response
    {
        http_response_code(503);
        include APP_PATH . '/Views/errors/maintenance.php';
        exit;
    }
}
