<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class ReportController extends BaseController
{
    public function index(Request $request): Response
    {
        $reports = Database::query(
            'SELECT * FROM reports ORDER BY created_at DESC LIMIT 50'
        );

        return $this->view('admin/reports/index', [
            'reports'    => $reports,
            'pageTitle'  => 'Reports',
            'activePage' => 'reports',
        ], 'admin/layouts/main');
    }

    public function eventIndex(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $reports = Database::query(
            'SELECT * FROM reports WHERE event_id = ? ORDER BY created_at DESC',
            [$eventId]
        );

        return $this->view('admin/reports/event-index', [
            'reports'    => $reports,
            'eventId'    => $eventId,
            'pageTitle'  => 'Event Reports',
            'activePage' => 'reports',
        ], 'admin/layouts/main');
    }

    public function generate(Request $request): Response
    {
        $eventId = (int) $request->input('event_id');
        $type    = $request->input('type', 'registrations');

        $id = Database::insert(
            'INSERT INTO reports (event_id, type, status, created_at) VALUES (?,?,\'pending\',NOW())',
            [$eventId, $type]
        );

        Session::flash('success', 'Report queued.');
        return Response::redirect('/admin/reports/' . $id);
    }

    public function show(Request $request): Response
    {
        $report = Database::queryOne(
            'SELECT * FROM reports WHERE id = ?', [(int) $request->param('id')]
        );

        if (!$report) {
            return Response::redirect('/admin/reports');
        }

        return $this->view('admin/reports/show', [
            'report'     => $report,
            'pageTitle'  => 'Report #' . $report['id'],
            'activePage' => 'reports',
        ], 'admin/layouts/main');
    }

    public function download(Request $request): Response
    {
        $report = Database::queryOne(
            'SELECT * FROM reports WHERE id = ?', [(int) $request->param('id')]
        );

        if (!$report || empty($report['file_path']) || !file_exists($report['file_path'])) {
            Session::flash('error', 'Report file not available.');
            return Response::redirect('/admin/reports');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($report['file_path']) . '"');
        readfile($report['file_path']);
        exit;
    }

    public function destroy(Request $request): Response
    {
        Database::execute('DELETE FROM reports WHERE id = ?', [(int) $request->param('id')]);
        Session::flash('success', 'Report deleted.');
        return Response::redirect('/admin/reports');
    }
}
