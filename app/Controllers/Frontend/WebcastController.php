<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Services\WebcastService;
use Core\Http\Request;
use Core\Http\Response;
use Core\Security\Sanitizer;

/**
 * WebcastController — handles the live webcast room.
 *
 * Routes:
 *   GET  /e/{slug}/watch           → room()
 *   POST /e/{slug}/watch/heartbeat → heartbeat()
 *
 * Both routes require: auth + verified + event + registered middleware.
 * By the time a method runs, the following request attributes are set:
 *   auth_user    → App\Models\User
 *   event        → App\Models\Event
 *   registration → App\Models\Registration
 */
class WebcastController extends BaseController
{
    private readonly WebcastService $service;

    public function __construct()
    {
        $this->service = new WebcastService();
    }

    /**
     * Render the webcast room.
     */
    public function room(Request $request): mixed
    {
        $event        = $request->getAttribute('event');
        $registration = $request->getAttribute('registration');

        if (!$event->isLive() && !$event->hasStarted()) {
            // Event hasn't started yet — redirect to landing page
            return Response::redirect('/e/' . $event->slug);
        }

        $webcastData  = $this->service->buildRoomData($event, $registration);
        $streamConfig = $webcastData['stream'];

        $inlineScript = 'window.WEBCAST = ' . json_encode($webcastData, JSON_HEX_TAG | JSON_HEX_AMP) . ';';

        return $this->view('frontend/webcast/room', [
            'event'        => $event,
            'registration' => $registration,
            'streamConfig' => $streamConfig,
            'webcastData'  => $webcastData,
            'inlineScript' => $inlineScript,
            'pageTitle'    => Sanitizer::e($event->title) . ' — Live',
            'bodyClass'    => 'webcast-page',
            'pageStyles'   => ['/assets/css/webcast.css'],
            'pageScripts'  => ['/assets/js/webcast.js'],
        ]);
    }

    /**
     * Attendance heartbeat — called every 60 s by webcast.js.
     * Returns a minimal JSON acknowledgement.
     */
    public function heartbeat(Request $request): mixed
    {
        $event        = $request->getAttribute('event');
        $registration = $request->getAttribute('registration');

        $this->service->recordHeartbeat((int) $event->id, (int) $registration->id);

        return Response::json(['ok' => true]);
    }
}
