<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Repositories\EventRepository;
use Core\Http\Request;
use Core\Http\Response;

class EventController
{
    private readonly EventRepository $eventRepo;

    public function __construct()
    {
        $this->eventRepo = new EventRepository();
    }

    public function index(Request $request): Response
    {
        $events = $this->eventRepo->allPublished();
        return Response::json(['events' => array_map(fn($e) => $e->toArray(), $events)]);
    }

    public function show(Request $request): Response
    {
        $event = $this->eventRepo->findBySlug($request->param('slug'));
        if (!$event) {
            return Response::json(['error' => 'Not found.'], 404);
        }
        return Response::json(['event' => $event->toArray()]);
    }
}
