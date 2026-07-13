<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\RegistrationReportService;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class RegistrationController extends BaseController
{
    private readonly RegistrationReportService $service;

    public function __construct()
    {
        $this->service = new RegistrationReportService();
    }

    /** Per-event registration list. */
    public function index(Request $request): mixed
    {
        $eventId = (int) $request->getAttribute('id');
        $search  = trim($request->query('search', ''));
        $status  = $request->query('status', '');
        $page    = max(1, (int) $request->query('page', 1));

        $result = $this->service->listForEvent($eventId, $search, $status, $page);

        return $this->view('admin/registrations/index', [
            'rows'      => $result['rows'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'pages'     => $result['pages'],
            'eventId'   => $eventId,
            'search'    => $search,
            'statusFilter' => $status,
            'pageTitle' => 'Registrations',
            'activePage'=> 'registrations',
        ], 'admin/layouts/main');
    }

    public function approve(Request $request): mixed
    {
        $rid = (int) $request->getAttribute('rid');
        $eid = (int) $request->getAttribute('id');
        $this->service->approve($rid);
        Session::flash('success', 'Registration approved.');
        return Response::redirect('/admin/events/' . $eid . '/registrations');
    }

    public function reject(Request $request): mixed
    {
        $rid = (int) $request->getAttribute('rid');
        $eid = (int) $request->getAttribute('id');
        $this->service->reject($rid);
        Session::flash('warning', 'Registration rejected.');
        return Response::redirect('/admin/events/' . $eid . '/registrations');
    }

    public function show(Request $request): mixed
    {
        return $this->view('admin/registrations/show', [
            'pageTitle' => 'Registration Detail', 'activePage' => 'registrations',
        ], 'admin/layouts/main');
    }

    public function export(Request $request): mixed { return Response::redirect('/admin'); }
    public function destroy(Request $request): mixed { return Response::redirect('/admin'); }
    public function bulkApprove(Request $request): mixed { return Response::redirect('/admin'); }
}
