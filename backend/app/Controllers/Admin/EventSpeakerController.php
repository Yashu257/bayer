<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\SpeakerService;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class EventSpeakerController extends BaseController
{
    private readonly SpeakerService $speakerService;

    public function __construct()
    {
        $this->speakerService = new SpeakerService();
    }

    public function index(Request $request): Response
    {
        $eventId  = (int) $request->param('eventId');
        $speakers = $this->speakerService->forEvent($eventId);
        $all      = $this->speakerService->all();

        return $this->view('admin/events/speakers', [
            'eventId'    => $eventId,
            'speakers'   => $speakers,
            'allSpeakers' => $all,
            'pageTitle'  => 'Event Speakers',
            'activePage' => 'events',
        ], 'admin/layouts/main');
    }

    public function attach(Request $request): Response
    {
        $eventId   = (int) $request->param('eventId');
        $speakerId = (int) $request->input('speaker_id');
        $this->speakerService->attach($eventId, $speakerId, [
            'role'       => $request->input('role', 'speaker'),
            'sort_order' => $request->input('sort_order', 0),
        ]);
        Session::flash('success', 'Speaker attached.');
        return Response::redirect('/admin/events/' . $eventId . '/speakers');
    }

    public function update(Request $request): Response
    {
        $eventId   = (int) $request->param('eventId');
        $speakerId = (int) $request->param('speakerId');
        $this->speakerService->attach($eventId, $speakerId, [
            'role'       => $request->input('role', 'speaker'),
            'sort_order' => $request->input('sort_order', 0),
        ]);
        Session::flash('success', 'Speaker updated.');
        return Response::redirect('/admin/events/' . $eventId . '/speakers');
    }

    public function detach(Request $request): Response
    {
        $eventId   = (int) $request->param('eventId');
        $speakerId = (int) $request->param('speakerId');
        $this->speakerService->detach($eventId, $speakerId);
        Session::flash('success', 'Speaker removed from event.');
        return Response::redirect('/admin/events/' . $eventId . '/speakers');
    }
}
