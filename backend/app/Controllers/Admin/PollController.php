<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\PollAdminService;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class PollController extends BaseController
{
    private readonly PollAdminService $service;

    public function __construct() { $this->service = new PollAdminService(); }

    public function index(Request $request): mixed
    {
        $eventId = (int) $request->getAttribute('id');
        return $this->view('admin/polls/index', [
            'rows'      => $this->service->listForEvent($eventId),
            'eventId'   => $eventId,
            'pageTitle' => 'Polls',
            'activePage'=> 'polls',
        ], 'admin/layouts/main');
    }

    public function launch(Request $request): mixed
    {
        $eventId = (int) $request->getAttribute('id');
        $this->service->launch((int) $request->getAttribute('pid'), $eventId);
        Session::flash('success', 'Poll launched.');
        return Response::redirect('/admin/events/' . $eventId . '/polls');
    }

    public function close(Request $request): mixed
    {
        $eventId = (int) $request->getAttribute('id');
        $this->service->close((int) $request->getAttribute('pid'));
        Session::flash('info', 'Poll closed.');
        return Response::redirect('/admin/events/' . $eventId . '/polls');
    }

    public function results(Request $request): mixed
    {
        $data = $this->service->getWithResults((int) $request->getAttribute('pid'));
        return $this->view('admin/polls/results', array_merge($data, [
            'eventId' => $request->getAttribute('id'), 'pageTitle' => 'Poll Results', 'activePage' => 'polls',
        ]), 'admin/layouts/main');
    }

    public function store(Request $r): mixed   { return Response::redirect('/admin'); }
    public function edit(Request $r): mixed    { return Response::redirect('/admin'); }
    public function update(Request $r): mixed  { return Response::redirect('/admin'); }
    public function destroy(Request $r): mixed { return Response::redirect('/admin'); }
}
