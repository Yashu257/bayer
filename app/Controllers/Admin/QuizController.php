<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Core\Http\Request;
use Core\Http\Response;

class QuizController extends BaseController
{
    public function index(Request $request): mixed
    {
        $eventId = (int) $request->getAttribute('id');
        return $this->view('admin/quiz/index', [
            'eventId'   => $eventId,
            'rows'      => [],
            'pageTitle' => 'Quiz',
            'activePage'=> 'quiz',
        ], 'admin/layouts/main');
    }

    public function store(Request $r): mixed   { return Response::redirect('/admin'); }
    public function edit(Request $r): mixed    { return Response::redirect('/admin'); }
    public function update(Request $r): mixed  { return Response::redirect('/admin'); }
    public function destroy(Request $r): mixed { return Response::redirect('/admin'); }
}
