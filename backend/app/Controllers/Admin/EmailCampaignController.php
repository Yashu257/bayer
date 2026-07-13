<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Core\Mail\MailQueue;
use Core\Database\DB;
use Core\Http\Request;
use Core\Http\Response;

class EmailCampaignController extends BaseController
{
    private const PER_PAGE = 50;

    public function index(Request $request): mixed
    {
        $status = $request->query('status', '');
        $page   = max(1, (int) $request->query('page', 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $where  = $status !== '' ? 'WHERE status = ?' : '';
        $params = $status !== '' ? [$status] : [];

        $total  = (int) (DB::query("SELECT COUNT(*) AS n FROM email_queue {$where}", $params)[0]['n'] ?? 0);
        $rows   = DB::query(
            "SELECT id, to_email, to_name, subject, status, attempts, max_attempts,
                    scheduled_at, sent_at, failed_at, error_message
               FROM email_queue {$where}
              ORDER BY id DESC
              LIMIT ? OFFSET ?",
            array_merge($params, [self::PER_PAGE, $offset])
        );

        return $this->view('admin/emails/index', [
            'rows'         => $rows,
            'total'        => $total,
            'page'         => $page,
            'pages'        => max(1, (int) ceil($total / self::PER_PAGE)),
            'counts'       => MailQueue::counts(),
            'statusFilter' => $status,
            'pageTitle'    => 'Email Queue',
            'activePage'   => 'emails',
        ], 'admin/layouts/main');
    }

    public function store(Request $r): mixed   { return Response::redirect('/admin/emails'); }
    public function show(Request $r): mixed    { return Response::redirect('/admin/emails'); }
    public function destroy(Request $r): mixed { return Response::redirect('/admin/emails'); }
}
