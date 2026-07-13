<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\FeedbackService;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class FeedbackController extends BaseController
{
    private readonly FeedbackService $feedbackService;

    public function __construct()
    {
        $this->feedbackService = new FeedbackService();
    }

    public function index(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $filters = $request->only(['rating', 'flagged']);
        $page    = max(1, (int) $request->query('page', 1));
        $result  = $this->feedbackService->list($eventId, $filters, $page);

        return $this->view('admin/feedback/index', array_merge($result, [
            'eventId'    => $eventId,
            'filters'    => $filters,
            'pageTitle'  => 'Feedback',
            'activePage' => 'feedback',
        ]), 'admin/layouts/main');
    }

    public function export(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $csv     = $this->feedbackService->exportCsv($eventId);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="feedback-event-' . $eventId . '.csv"');
        echo $csv;
        exit;
    }

    public function flag(Request $request): Response
    {
        $this->feedbackService->flag((int) $request->param('id'));
        Session::flash('success', 'Feedback flagged.');
        return Response::redirect('/admin/events/' . $request->param('eventId') . '/feedback');
    }

    public function hide(Request $request): Response
    {
        $this->feedbackService->hide((int) $request->param('id'));
        Session::flash('success', 'Feedback hidden.');
        return Response::redirect('/admin/events/' . $request->param('eventId') . '/feedback');
    }
}
