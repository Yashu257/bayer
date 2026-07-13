<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\LandingPage;
use Core\Database\Database;

class LandingPageRepository
{
    public function findByEventId(int $eventId): ?LandingPage
    {
        $row = Database::queryOne(
            "SELECT * FROM landing_pages WHERE event_id = ? LIMIT 1",
            [$eventId]
        );
        return $row ? new LandingPage($row) : null;
    }

    /** Speakers assigned to the event, ordered by sort_order. */
    public function speakersByEvent(int $eventId): array
    {
        return Database::query(
            "SELECT s.*, es.role AS event_role, es.sort_order
             FROM speakers s
             JOIN event_speakers es ON es.speaker_id = s.id
             WHERE es.event_id = ?
               AND s.deleted_at IS NULL
               AND s.status = 'active'
             ORDER BY es.sort_order ASC",
            [$eventId]
        );
    }

    /** Sponsors assigned to the event, ordered by tier then sort_order. */
    public function sponsorsByEvent(int $eventId): array
    {
        return Database::query(
            "SELECT * FROM sponsors
             WHERE event_id = ? AND status = 'active'
             ORDER BY FIELD(tier,'platinum','gold','silver','bronze','exhibitor'), sort_order ASC",
            [$eventId]
        );
    }

    /** Webcast agenda items for the event. */
    public function agendaByEvent(int $eventId): array
    {
        return Database::query(
            "SELECT a.*, s.first_name, s.last_name, s.job_title, s.photo_path
             FROM webcast_agenda_items a
             LEFT JOIN speakers s ON s.id = a.speaker_id
             JOIN webcasts w ON w.id = a.webcast_id
             WHERE w.event_id = ? AND a.status = 'active'
             ORDER BY a.sort_order ASC",
            [$eventId]
        );
    }
}
