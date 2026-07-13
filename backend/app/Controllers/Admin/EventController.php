<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\EventService;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class EventController extends BaseController
{
    private readonly EventService $eventService;

    public function __construct()
    {
        $this->eventService = new EventService();
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'search']);
        $page    = max(1, (int) $request->query('page', 1));
        $result  = $this->eventService->list($filters, $page);

        return $this->view('admin/events/index', array_merge($result, [
            'filters'    => $filters,
            'pageTitle'  => 'Events',
            'activePage' => 'events',
        ]), 'admin/layouts/main');
    }

    public function create(Request $request): Response
    {
        return $this->view('admin/events/create', [
            'pageTitle'  => 'Create Event',
            'activePage' => 'events',
        ], 'admin/layouts/main');
    }

    public function store(Request $request): Response
    {
        $data = $request->only([
            'title', 'description', 'starts_at', 'ends_at',
            'status', 'max_capacity', 'registration_deadline',
        ]);

        $id = $this->eventService->create($data);
        Session::flash('success', 'Event created.');
        return Response::redirect('/admin/events/' . $id);
    }

    public function show(Request $request): Response
    {
        $event = $this->eventService->findById((int) $request->param('id'));
        if (!$event) {
            return Response::redirect('/admin/events');
        }

        return $this->view('admin/events/show', [
            'event'      => $event,
            'pageTitle'  => $event['title'],
            'activePage' => 'events',
        ], 'admin/layouts/main');
    }

    public function edit(Request $request): Response
    {
        $event = $this->eventService->findById((int) $request->param('id'));
        if (!$event) {
            return Response::redirect('/admin/events');
        }

        return $this->view('admin/events/edit', [
            'event'      => $event,
            'pageTitle'  => 'Edit: ' . $event['title'],
            'activePage' => 'events',
        ], 'admin/layouts/main');
    }

    public function update(Request $request): Response
    {
        $id   = (int) $request->param('id');
        $data = $request->only([
            'title', 'description', 'starts_at', 'ends_at',
            'status', 'max_capacity', 'registration_deadline',
        ]);

        $this->eventService->update($id, $data);
        Session::flash('success', 'Event updated.');
        return Response::redirect('/admin/events/' . $id);
    }

    public function destroy(Request $request): Response
    {
        $this->eventService->delete((int) $request->param('id'));
        Session::flash('success', 'Event deleted.');
        return Response::redirect('/admin/events');
    }

    public function updateStatus(Request $request): Response
    {
        $id     = (int) $request->param('id');
        $status = $request->input('status', '');
        $this->eventService->updateStatus($id, $status);
        Session::flash('success', 'Status updated.');
        return Response::redirect('/admin/events/' . $id);
    }

    public function clone(Request $request): Response
    {
        $newId = $this->eventService->clone((int) $request->param('id'));
        Session::flash('success', 'Event cloned.');
        return Response::redirect('/admin/events/' . $newId . '/edit');
    }
}
