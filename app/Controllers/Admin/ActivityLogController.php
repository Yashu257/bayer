<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\ActivityLogAdminService;
use Core\Http\Request;
use Core\Http\Response;

class ActivityLogController extends BaseController
{
    private readonly ActivityLogAdminService $service;

    public function __construct() { $this->service = new ActivityLogAdminService(); }

    public function index(Request $request): mixed
    {
        $search = trim($request->query('search', ''));
        $action = $request->query('action', '');
        $page   = max(1, (int) $request->query('page', 1));
        $result = $this->service->list($search, $action, $page);

        return $this->view('admin/activity-logs/index', [
            'rows'      => $result['rows'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'pages'     => $result['pages'],
            'search'    => $search,
            'action'    => $action,
            'actions'   => $this->service->distinctActions(),
            'pageTitle' => 'Activity Logs',
            'activePage'=> 'activity-logs',
        ], 'admin/layouts/main');
    }

    public function eventIndex(Request $request): mixed { return $this->index($request); }
    public function export(Request $request): mixed     { return Response::redirect('/admin'); }
}
