<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\WebcastService;
use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class WebcastController extends BaseController
{
    private readonly WebcastService $webcastService;

    public function __construct()
    {
        $this->webcastService = new WebcastService();
    }

    public function show(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $webcast = $this->webcastService->getForEvent($eventId);

        return $this->view('admin/webcasts/show', [
            'webcast'    => $webcast,
            'eventId'    => $eventId,
            'pageTitle'  => 'Live Webcast',
            'activePage' => 'webcast',
        ], 'admin/layouts/main');
    }

    public function setup(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $webcast = $this->webcastService->getForEvent($eventId);

        return $this->view('admin/webcasts/setup', [
            'webcast'    => $webcast,
            'eventId'    => $eventId,
            'pageTitle'  => 'Webcast Setup',
            'activePage' => 'webcast',
        ], 'admin/layouts/main');
    }

    public function update(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $data    = $request->only([
            'provider', 'stream_url', 'stream_key', 'chat_enabled',
            'qa_enabled', 'polls_enabled', 'recording_enabled',
        ]);

        $this->webcastService->configure($eventId, $data);
        Session::flash('success', 'Webcast settings saved.');
        return Response::redirect('/admin/events/' . $eventId . '/webcast/setup');
    }

    public function goLive(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $this->webcastService->goLive($eventId);
        Session::flash('success', 'Webcast is now LIVE.');
        return Response::redirect('/admin/events/' . $eventId . '/webcast');
    }

    public function pause(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $this->webcastService->pause($eventId);
        Session::flash('success', 'Webcast paused.');
        return Response::redirect('/admin/events/' . $eventId . '/webcast');
    }

    public function end(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $this->webcastService->end($eventId);
        Session::flash('success', 'Webcast ended.');
        return Response::redirect('/admin/events/' . $eventId . '/webcast');
    }

    public function backstage(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');

        return $this->view('admin/webcasts/backstage', [
            'eventId'    => $eventId,
            'pageTitle'  => 'Backstage',
            'activePage' => 'webcast',
        ], 'admin/layouts/main');
    }
}
