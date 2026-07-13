<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\AttendeeService;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class UserController extends BaseController
{
    private readonly AttendeeService $service;

    public function __construct()
    {
        $this->service = new AttendeeService();
    }

    public function index(Request $request): mixed
    {
        $search = trim($request->query('search', ''));
        $status = $request->query('status', '');
        $page   = max(1, (int) $request->query('page', 1));

        $result = $this->service->list($search, $status, $page);

        return $this->view('admin/attendees/index', [
            'rows'      => $result['rows'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'pages'     => $result['pages'],
            'search'    => $search,
            'status'    => $status,
            'pageTitle' => 'Attendees',
            'activePage'=> 'attendees',
        ], 'admin/layouts/main');
    }

    public function updateStatus(Request $request): mixed
    {
        $uid    = (int) $request->getAttribute('uid');
        $status = $request->input('status', '');

        if (!in_array($status, ['active', 'inactive', 'banned'], true)) {
            return Response::json(['error' => 'Invalid status.'], 422);
        }

        $this->service->updateStatus($uid, $status);
        Session::flash('success', 'Status updated.');

        return Response::redirect('/admin/users');
    }

    public function show(Request $request): mixed
    {
        return $this->view('admin/attendees/show', [
            'pageTitle'  => 'Attendee Detail',
            'activePage' => 'attendees',
        ], 'admin/layouts/main');
    }

    public function destroy(Request $request): mixed
    {
        return Response::redirect('/admin/users');
    }

    public function export(Request $request): mixed
    {
        return Response::redirect('/admin/users');
    }

    public function impersonate(Request $request): mixed
    {
        return Response::redirect('/admin/users');
    }
}
