<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\LandingPage;
use App\Repositories\EventRepository;
use App\Repositories\LandingPageRepository;
use Core\Exceptions\HttpException;

/**
 * LandingPageService — assembles all data needed to render a landing page.
 * One call, one structured array — no extra queries in the controller or view.
 */
class LandingPageService
{
    private readonly EventRepository       $events;
    private readonly LandingPageRepository $pages;

    public function __construct()
    {
        $this->events = new EventRepository();
        $this->pages  = new LandingPageRepository();
    }

    /**
     * Resolve all data needed to render /e/{slug}.
     *
     * @throws HttpException 404 if event not found or not publicly visible
     * @return array{
     *   event:       Event,
     *   page:        LandingPage|null,
     *   speakers:    array,
     *   sponsors:    array,
     *   agenda:      array,
     *   countdown:   int,       seconds until event starts
     *   metaTitle:   string,
     *   metaDesc:    string,
     * }
     */
    public function resolveForPublic(string $slug): array
    {
        $event = $this->events->findBySlug($slug);

        if ($event === null || !$event->isOpen()) {
            throw new HttpException(404, 'Event not found.');
        }

        $page     = $this->pages->findByEventId((int) $event->id);
        $speakers = [];
        $sponsors = [];
        $agenda   = [];

        if ($page !== null) {
            $speakers = $page->speakers_visible ? $this->pages->speakersByEvent((int) $event->id) : [];
            $sponsors = $page->sponsors_visible ? $this->pages->sponsorsByEvent((int) $event->id) : [];
            $agenda   = $page->agenda_visible   ? $this->pages->agendaByEvent((int) $event->id)   : [];
        }

        return [
            'event'     => $event,
            'page'      => $page,
            'speakers'  => $speakers,
            'sponsors'  => $sponsors,
            'agenda'    => $agenda,
            'countdown' => $event->secondsUntilStart(),
            'metaTitle' => $page?->meta_title        ?? $event->title,
            'metaDesc'  => $page?->meta_description  ?? $event->short_description ?? '',
        ];
    }
}
