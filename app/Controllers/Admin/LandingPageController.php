<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\LandingPageService;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class LandingPageController extends BaseController
{
    private readonly LandingPageService $lpService;

    public function __construct()
    {
        $this->lpService = new LandingPageService();
    }

    public function edit(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $page    = $this->lpService->getForEvent($eventId);

        return $this->view('admin/landing-pages/edit', [
            'page'       => $page,
            'eventId'    => $eventId,
            'pageTitle'  => 'Landing Page Editor',
            'activePage' => 'events',
        ], 'admin/layouts/main');
    }

    public function update(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $data    = $request->only(['hero_title', 'hero_subtitle', 'body_html', 'cta_label']);

        $this->lpService->save($eventId, $data);
        Session::flash('success', 'Landing page saved.');
        return Response::redirect('/admin/events/' . $eventId . '/landing');
    }

    public function publish(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $this->lpService->publish($eventId);
        Session::flash('success', 'Landing page published.');
        return Response::redirect('/admin/events/' . $eventId . '/landing');
    }
}
