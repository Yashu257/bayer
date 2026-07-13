<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class RegistrationFormController extends BaseController
{
    public function edit(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $fields  = Database::query(
            'SELECT * FROM registration_form_fields WHERE event_id = ? ORDER BY sort_order ASC',
            [$eventId]
        );

        return $this->view('admin/events/registration-form', [
            'eventId'    => $eventId,
            'fields'     => $fields,
            'pageTitle'  => 'Registration Form',
            'activePage' => 'events',
        ], 'admin/layouts/main');
    }

    public function update(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $fields  = $request->input('fields', []);

        foreach ((array) $fields as $fieldId => $data) {
            Database::execute(
                'UPDATE registration_form_fields SET label=?, required=?, sort_order=? WHERE id=? AND event_id=?',
                [$data['label'] ?? '', (int)($data['required'] ?? 0), (int)($data['sort_order'] ?? 0), (int)$fieldId, $eventId]
            );
        }

        Session::flash('success', 'Form updated.');
        return Response::redirect('/admin/events/' . $eventId . '/form');
    }

    public function addField(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $data    = $request->only(['label', 'type', 'required', 'sort_order', 'options']);

        Database::insert(
            'INSERT INTO registration_form_fields (event_id, label, type, required, sort_order, options, created_at)
             VALUES (?,?,?,?,?,?,NOW())',
            [$eventId, $data['label'] ?? '', $data['type'] ?? 'text',
             (int)($data['required'] ?? 0), (int)($data['sort_order'] ?? 0),
             $data['options'] ?? null]
        );

        Session::flash('success', 'Field added.');
        return Response::redirect('/admin/events/' . $eventId . '/form');
    }

    public function updateField(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $fieldId = (int) $request->param('fieldId');
        $data    = $request->only(['label', 'type', 'required', 'sort_order', 'options']);

        Database::execute(
            'UPDATE registration_form_fields SET label=?, type=?, required=?, sort_order=?, options=?, updated_at=NOW()
              WHERE id=? AND event_id=?',
            [$data['label'] ?? '', $data['type'] ?? 'text', (int)($data['required'] ?? 0),
             (int)($data['sort_order'] ?? 0), $data['options'] ?? null, $fieldId, $eventId]
        );

        Session::flash('success', 'Field updated.');
        return Response::redirect('/admin/events/' . $eventId . '/form');
    }

    public function deleteField(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $fieldId = (int) $request->param('fieldId');

        Database::execute(
            'DELETE FROM registration_form_fields WHERE id = ? AND event_id = ?',
            [$fieldId, $eventId]
        );

        Session::flash('success', 'Field deleted.');
        return Response::redirect('/admin/events/' . $eventId . '/form');
    }
}
