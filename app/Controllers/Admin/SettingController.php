<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\SettingsService;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class SettingController extends BaseController
{
    private readonly SettingsService $service;

    public function __construct() { $this->service = new SettingsService(); }

    public function index(Request $request): mixed
    {
        return $this->view('admin/settings/index', [
            'grouped'   => $this->service->getAll(),
            'pageTitle' => 'Platform Settings',
            'activePage'=> 'settings',
        ], 'admin/layouts/main');
    }

    public function update(Request $request): mixed
    {
        $this->service->save($request->all());
        Session::flash('success', 'Settings saved.');
        return Response::redirect('/admin/settings');
    }

    public function eventSettings(Request $request): mixed
    {
        $eventId = (int) $request->getAttribute('id');
        return $this->view('admin/settings/event', [
            'grouped'   => $this->service->getAll($eventId),
            'eventId'   => $eventId,
            'pageTitle' => 'Event Settings',
            'activePage'=> 'settings',
        ], 'admin/layouts/main');
    }

    public function updateEvent(Request $request): mixed
    {
        $eventId = (int) $request->getAttribute('id');
        $this->service->save($request->all(), $eventId);
        Session::flash('success', 'Event settings saved.');
        return Response::redirect('/admin/events/' . $eventId . '/settings');
    }
}
