<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Core\Database\Database;

class PollAdminService
{
    public function listForEvent(int $eventId): array
    {
        $polls = Database::query(
            'SELECT p.id, p.question, p.status, p.created_at,
                    SUM(po.vote_count) AS total_votes
               FROM polls p
               LEFT JOIN poll_options po ON po.poll_id = p.id
              WHERE p.event_id = ? AND p.deleted_at IS NULL
              GROUP BY p.id
              ORDER BY p.created_at DESC',
            [$eventId]
        );

        return $polls;
    }

    public function getWithResults(int $pollId): array
    {
        $poll    = Database::queryOne('SELECT * FROM polls WHERE id = ?', [$pollId]);
        $options = Database::query(
            'SELECT id, option_text, vote_count FROM poll_options WHERE poll_id = ? ORDER BY display_order',
            [$pollId]
        );
        $total   = array_sum(array_column($options, 'vote_count'));

        return compact('poll', 'options', 'total');
    }

    public function launch(int $pollId, int $eventId): void
    {
        // Only one poll active at a time per event
        Database::execute(
            'UPDATE polls SET status = "closed" WHERE event_id = ? AND status = "active"',
            [$eventId]
        );
        Database::execute(
            'UPDATE polls SET status = "active", updated_at = NOW() WHERE id = ?',
            [$pollId]
        );
    }

    public function close(int $pollId): void
    {
        Database::execute(
            'UPDATE polls SET status = "closed", updated_at = NOW() WHERE id = ?',
            [$pollId]
        );
    }
}
