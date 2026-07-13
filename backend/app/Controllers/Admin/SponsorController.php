<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class SponsorController extends BaseController
{
    public function index(Request $request): Response
    {
        $eventId  = (int) $request->param('eventId');
        $sponsors = Database::query(
            'SELECT * FROM sponsors WHERE event_id = ? ORDER BY sort_order ASC', [$eventId]
        );

        return $this->view('admin/events/sponsors', [
            'eventId'    => $eventId,
            'sponsors'   => $sponsors,
            'pageTitle'  => 'Sponsors',
            'activePage' => 'events',
        ], 'admin/layouts/main');
    }

    public function store(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $data    = $request->only(['name', 'logo_url', 'website_url', 'tier', 'sort_order']);

        Database::insert(
            'INSERT INTO sponsors (event_id, name, logo_url, website_url, tier, sort_order, created_at)
             VALUES (?,?,?,?,?,?,NOW())',
            [$eventId, $data['name'], $data['logo_url'] ?? null,
             $data['website_url'] ?? null, $data['tier'] ?? 'bronze', (int)($data['sort_order'] ?? 0)]
        );

        Session::flash('success', 'Sponsor added.');
        return Response::redirect('/admin/events/' . $eventId . '/sponsors');
    }

    public function update(Request $request): Response
    {
        $eventId   = (int) $request->param('eventId');
        $sponsorId = (int) $request->param('id');
        $data      = $request->only(['name', 'logo_url', 'website_url', 'tier', 'sort_order']);

        Database::execute(
            'UPDATE sponsors SET name=?, logo_url=?, website_url=?, tier=?, sort_order=?, updated_at=NOW()
              WHERE id=? AND event_id=?',
            [$data['name'], $data['logo_url'] ?? null, $data['website_url'] ?? null,
             $data['tier'] ?? 'bronze', (int)($data['sort_order'] ?? 0), $sponsorId, $eventId]
        );

        Session::flash('success', 'Sponsor updated.');
        return Response::redirect('/admin/events/' . $eventId . '/sponsors');
    }

    public function destroy(Request $request): Response
    {
        $eventId   = (int) $request->param('eventId');
        $sponsorId = (int) $request->param('id');

        Database::execute('DELETE FROM sponsors WHERE id = ? AND event_id = ?', [$sponsorId, $eventId]);
        Session::flash('success', 'Sponsor removed.');
        return Response::redirect('/admin/events/' . $eventId . '/sponsors');
    }
}
