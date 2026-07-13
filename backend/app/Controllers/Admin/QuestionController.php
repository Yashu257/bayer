<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\QuestionAdminService;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class QuestionController extends BaseController
{
    private readonly QuestionAdminService $service;

    public function __construct()
    {
        $this->service = new QuestionAdminService();
    }

    public function index(Request $request): mixed
    {
        $eventId = (int) $request->getAttribute('id');
        $filter  = $request->query('filter', 'all');
        $rows    = $this->service->listForEvent($eventId, $filter);

        return $this->view('admin/questions/index', [
            'rows'      => $rows,
            'eventId'   => $eventId,
            'filter'    => $filter,
            'pageTitle' => 'Q&A Moderation',
            'activePage'=> 'questions',
        ], 'admin/layouts/main');
    }

    public function approve(Request $request): mixed
    {
        $this->service->approve((int) $request->getAttribute('qid'));
        if ($request->isAjax()) return Response::json(['ok' => true]);
        Session::flash('success', 'Question approved.');
        return Response::redirect('/admin/events/' . $request->getAttribute('id') . '/questions');
    }

    public function dismiss(Request $request): mixed
    {
        $this->service->dismiss((int) $request->getAttribute('qid'));
        if ($request->isAjax()) return Response::json(['ok' => true]);
        Session::flash('info', 'Question dismissed.');
        return Response::redirect('/admin/events/' . $request->getAttribute('id') . '/questions');
    }

    public function answer(Request $request): mixed
    {
        $this->service->answer((int) $request->getAttribute('qid'), $request->input('answer_text', ''));
        Session::flash('success', 'Answer saved.');
        return Response::redirect('/admin/events/' . $request->getAttribute('id') . '/questions');
    }

    public function destroy(Request $request): mixed
    {
        $this->service->delete((int) $request->getAttribute('qid'));
        return Response::redirect('/admin/events/' . $request->getAttribute('id') . '/questions');
    }

    public function export(Request $request): mixed { return Response::redirect('/admin'); }
}
