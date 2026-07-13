<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\AttendanceAdminService;
use Core\Http\Request;
use Core\Http\Response;

class AttendanceController extends BaseController
{
    private readonly AttendanceAdminService $service;

    public function __construct() { $this->service = new AttendanceAdminService(); }

    public function index(Request $request): mixed
    {
        $eventId = (int) $request->getAttribute('id');
        $page    = max(1, (int) $request->query('page', 1));
        $result  = $this->service->listForEvent($eventId, $page);
        $summary = $this->service->summaryForEvent($eventId);

        return $this->view('admin/attendance/index', [
            'rows'      => $result['rows'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'pages'     => $result['pages'],
            'summary'   => $summary,
            'eventId'   => $eventId,
            'pageTitle' => 'Attendance',
            'activePage'=> 'attendance',
        ], 'admin/layouts/main');
    }

    public function live(Request $request): mixed
    {
        $eventId = (int) $request->getAttribute('id');
        return Response::json(['live' => $this->service->liveCount($eventId)]);
    }

    public function export(Request $request): mixed { return Response::redirect('/admin'); }
}
