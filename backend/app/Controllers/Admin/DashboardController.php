<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\DashboardService;
use Core\Http\Request;
use Core\Http\Response;

class DashboardController extends BaseController
{
    private readonly DashboardService $service;

    public function __construct()
    {
        $this->service = new DashboardService();
    }

    public function index(Request $request): mixed
    {
        // Render preview.admin directly without layout
        ob_start();
        include APP_PATH . '/Views/admin/dashboard/index.php';
        return Response::make(ob_get_clean());
    }

    /** AJAX endpoint — refreshed stats card values. */
    public function stats(Request $request): mixed
    {
        return Response::json($this->service->getKpis());
    }
}
