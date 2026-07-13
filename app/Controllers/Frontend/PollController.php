<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class PollController extends BaseController
{
    public function active(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');

        $poll = Database::queryOne(
            "SELECT p.*, GROUP_CONCAT(po.id ORDER BY po.sort_order SEPARATOR '|') AS option_ids,
                    GROUP_CONCAT(po.option_text ORDER BY po.sort_order SEPARATOR '|') AS option_texts
               FROM polls p
               LEFT JOIN poll_options po ON po.poll_id = p.id
              WHERE p.event_id = ? AND p.status = 'live'
              GROUP BY p.id
              LIMIT 1",
            [$eventId]
        );

        if (!$poll) {
            return Response::json(['poll' => null]);
        }

        $ids   = explode('|', $poll['option_ids']   ?? '');
        $texts = explode('|', $poll['option_texts'] ?? '');
        $options = [];
        foreach ($ids as $i => $oid) {
            $options[] = ['id' => (int) $oid, 'text' => $texts[$i] ?? ''];
        }
        $poll['options'] = $options;
        unset($poll['option_ids'], $poll['option_texts']);

        return Response::json(['poll' => $poll]);
    }

    public function vote(Request $request): Response
    {
        $user     = Session::get('auth_user');
        $pollId   = (int) $request->param('id');
        $optionId = (int) $request->input('option_id');

        $exists = Database::queryOne(
            'SELECT id FROM poll_responses WHERE poll_id = ? AND user_id = ?',
            [$pollId, $user['id']]
        );

        if ($exists) {
            return Response::json(['error' => 'Already voted.'], 409);
        }

        Database::insert(
            'INSERT INTO poll_responses (poll_id, poll_option_id, user_id, created_at) VALUES (?,?,?,NOW())',
            [$pollId, $optionId, $user['id']]
        );

        return Response::json(['message' => 'Vote recorded.']);
    }

    public function results(Request $request): Response
    {
        $pollId = (int) $request->param('id');

        $options = Database::query(
            'SELECT po.id, po.option_text,
                    COUNT(pr.id) AS votes
               FROM poll_options po
               LEFT JOIN poll_responses pr ON pr.poll_option_id = po.id
              WHERE po.poll_id = ?
              GROUP BY po.id, po.option_text
              ORDER BY po.sort_order ASC',
            [$pollId]
        );

        $total = array_sum(array_column($options, 'votes'));

        foreach ($options as &$opt) {
            $opt['pct'] = $total > 0 ? round($opt['votes'] / $total * 100, 1) : 0;
        }
        unset($opt);

        return Response::json(['options' => $options, 'total' => $total]);
    }
}
