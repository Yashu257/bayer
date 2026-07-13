<?php

declare(strict_types=1);

namespace App\Controllers\Api\Admin;

use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;

class PollController
{
    public function launch(Request $request): Response
    {
        $pollId = (int) $request->param('id');
        Database::execute("UPDATE polls SET status='live', launched_at=NOW() WHERE id=?", [$pollId]);
        return Response::json(['status' => 'live']);
    }

    public function close(Request $request): Response
    {
        $pollId = (int) $request->param('id');
        Database::execute("UPDATE polls SET status='closed', closed_at=NOW() WHERE id=?", [$pollId]);
        return Response::json(['status' => 'closed']);
    }

    public function results(Request $request): Response
    {
        $pollId  = (int) $request->param('id');
        $options = Database::query(
            'SELECT po.id, po.option_text, COUNT(pr.id) AS votes
               FROM poll_options po
               LEFT JOIN poll_responses pr ON pr.poll_option_id = po.id
              WHERE po.poll_id = ?
              GROUP BY po.id
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
