<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Core\Http\Request;
use Core\Http\Response;

class SurveyController extends BaseController
{
    public function index(Request $request): mixed
    {
        $eventId = (int) $request->getAttribute('id');
        return $this->view('admin/surveys/index', [
            'eventId'   => $eventId,
            'rows'      => [],
            'pageTitle' => 'Surveys',
            'activePage'=> 'surveys',
        ], 'admin/layouts/main');
    }

    public function results(Request $request): mixed
    {
        return Response::redirect('/admin');
    }

    public function store(Request $r): mixed   { return Response::redirect('/admin'); }
    public function destroy(Request $r): mixed { return Response::redirect('/admin'); }
}
