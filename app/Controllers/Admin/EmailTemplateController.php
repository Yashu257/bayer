<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class EmailTemplateController extends BaseController
{
    public function index(Request $request): Response
    {
        $templates = Database::query(
            'SELECT * FROM email_templates ORDER BY name ASC'
        );

        return $this->view('admin/email-templates/index', [
            'templates'  => $templates,
            'pageTitle'  => 'Email Templates',
            'activePage' => 'emails',
        ], 'admin/layouts/main');
    }

    public function create(Request $request): Response
    {
        return $this->view('admin/email-templates/create', [
            'pageTitle'  => 'New Template',
            'activePage' => 'emails',
        ], 'admin/layouts/main');
    }

    public function store(Request $request): Response
    {
        $data = $request->only(['name', 'subject', 'html_body', 'text_body', 'type']);
        Database::insert(
            'INSERT INTO email_templates (name, subject, html_body, text_body, type, created_at)
             VALUES (?,?,?,?,?,NOW())',
            [$data['name'], $data['subject'], $data['html_body'], $data['text_body'] ?? '', $data['type'] ?? 'custom']
        );
        Session::flash('success', 'Template created.');
        return Response::redirect('/admin/email-templates');
    }

    public function edit(Request $request): Response
    {
        $template = Database::queryOne(
            'SELECT * FROM email_templates WHERE id = ?', [(int) $request->param('id')]
        );

        if (!$template) {
            return Response::redirect('/admin/email-templates');
        }

        return $this->view('admin/email-templates/edit', [
            'template'   => $template,
            'pageTitle'  => 'Edit Template',
            'activePage' => 'emails',
        ], 'admin/layouts/main');
    }

    public function update(Request $request): Response
    {
        $id   = (int) $request->param('id');
        $data = $request->only(['name', 'subject', 'html_body', 'text_body', 'type']);

        Database::execute(
            'UPDATE email_templates SET name=?,subject=?,html_body=?,text_body=?,type=?,updated_at=NOW() WHERE id=?',
            [$data['name'], $data['subject'], $data['html_body'], $data['text_body'] ?? '', $data['type'] ?? 'custom', $id]
        );
        Session::flash('success', 'Template updated.');
        return Response::redirect('/admin/email-templates');
    }

    public function destroy(Request $request): Response
    {
        Database::execute('DELETE FROM email_templates WHERE id = ?', [(int) $request->param('id')]);
        Session::flash('success', 'Template deleted.');
        return Response::redirect('/admin/email-templates');
    }

    public function preview(Request $request): Response
    {
        $template = Database::queryOne(
            'SELECT * FROM email_templates WHERE id = ?', [(int) $request->param('id')]
        );

        if (!$template) {
            return Response::redirect('/admin/email-templates');
        }

        echo $template['html_body'];
        exit;
    }
}
