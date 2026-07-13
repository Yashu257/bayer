<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class PollController
{
    public function active(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');

        $poll = Database::queryOne(
            "SELECT * FROM polls WHERE event_id = ? AND status = 'live' LIMIT 1", [$eventId]
        );

        if (!$poll) {
            return Response::json(['poll' => null]);
        }

        $poll['options'] = Database::query(
            'SELECT id, option_text, sort_order FROM poll_options WHERE poll_id = ? ORDER BY sort_order',
            [$poll['id']]
        );

        return Response::json(['poll' => $poll]);
    }

    public function vote(Request $request): Response
    {
        $user     = Session::get('auth_user');
        $pollId   = (int) $request->param('id');
        $body     = $request->isJson() ? $request->json() : $request->all();
        $optionId = (int)($body['option_id'] ?? 0);

        $exists = Database::queryOne(
            'SELECT id FROM poll_responses WHERE poll_id = ? AND user_id = ?', [$pollId, $user['id']]
        );
        if ($exists) {
            return Response::json(['error' => 'Already voted.'], 409);
        }

        Database::insert(
            'INSERT INTO poll_responses (poll_id, poll_option_id, user_id, created_at) VALUES (?,?,?,NOW())',
            [$pollId, $optionId, $user['id']]
        );

        return Response::json(['voted' => true]);
    }

    public function results(Request $request): Response
    {
        $pollId  = (int) $request->param('id');
        $options = Database::query(
            'SELECT po.id, po.option_text, COUNT(pr.id) AS votes
               FROM poll_options po
               LEFT JOIN poll_responses pr ON pr.poll_option_id = po.id
              WHERE po.poll_id = ?
              GROUP BY po.id, po.option_text
              ORDER BY po.sort_order',
            [$pollId]
        );

        $total = array_sum(array_column($options, 'votes'));
        foreach ($options as &$o) {
            $o['pct'] = $total > 0 ? round($o['votes'] / $total * 100, 1) : 0.0;
        }
        unset($o);

        return Response::json(['options' => $options, 'total' => $total]);
    }
}
