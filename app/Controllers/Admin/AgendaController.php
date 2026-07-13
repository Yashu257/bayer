<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class AgendaController extends BaseController
{
    public function store(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $data    = $request->only(['title', 'description', 'starts_at', 'ends_at', 'speaker_id', 'sort_order']);

        Database::insert(
            'INSERT INTO agenda_items (event_id, title, description, starts_at, ends_at, speaker_id, sort_order, created_at)
             VALUES (?,?,?,?,?,?,?,NOW())',
            [$eventId, $data['title'] ?? '', $data['description'] ?? '',
             $data['starts_at'] ?? null, $data['ends_at'] ?? null,
             $data['speaker_id'] ? (int)$data['speaker_id'] : null,
             (int)($data['sort_order'] ?? 0)]
        );

        Session::flash('success', 'Agenda item added.');
        return Response::redirect('/admin/events/' . $eventId . '/agenda');
    }

    public function update(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $itemId  = (int) $request->param('itemId');
        $data    = $request->only(['title', 'description', 'starts_at', 'ends_at', 'speaker_id', 'sort_order']);

        Database::execute(
            'UPDATE agenda_items SET title=?,description=?,starts_at=?,ends_at=?,speaker_id=?,sort_order=?,updated_at=NOW()
              WHERE id=? AND event_id=?',
            [$data['title'] ?? '', $data['description'] ?? '',
             $data['starts_at'] ?? null, $data['ends_at'] ?? null,
             $data['speaker_id'] ? (int)$data['speaker_id'] : null,
             (int)($data['sort_order'] ?? 0), $itemId, $eventId]
        );

        Session::flash('success', 'Agenda item updated.');
        return Response::redirect('/admin/events/' . $eventId . '/agenda');
    }

    public function destroy(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $itemId  = (int) $request->param('itemId');

        Database::execute('DELETE FROM agenda_items WHERE id=? AND event_id=?', [$itemId, $eventId]);
        Session::flash('success', 'Agenda item deleted.');
        return Response::redirect('/admin/events/' . $eventId . '/agenda');
    }

    public function reorder(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $order   = $request->input('order', []);

        foreach ((array) $order as $sort => $itemId) {
            Database::execute(
                'UPDATE agenda_items SET sort_order=? WHERE id=? AND event_id=?',
                [(int)$sort, (int)$itemId, $eventId]
            );
        }

        return Response::json(['reordered' => true]);
    }
}
