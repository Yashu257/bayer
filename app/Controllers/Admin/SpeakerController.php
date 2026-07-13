<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\SpeakerService;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class SpeakerController extends BaseController
{
    private readonly SpeakerService $speakerService;

    public function __construct()
    {
        $this->speakerService = new SpeakerService();
    }

    public function index(Request $request): Response
    {
        return $this->view('admin/speakers/index', [
            'speakers'   => $this->speakerService->all(),
            'pageTitle'  => 'Speakers',
            'activePage' => 'speakers',
        ], 'admin/layouts/main');
    }

    public function create(Request $request): Response
    {
        return $this->view('admin/speakers/create', [
            'pageTitle'  => 'Add Speaker',
            'activePage' => 'speakers',
        ], 'admin/layouts/main');
    }

    public function store(Request $request): Response
    {
        $data = $request->only(['name', 'title', 'bio', 'photo_url', 'linkedin_url']);
        $this->speakerService->create($data);
        Session::flash('success', 'Speaker added.');
        return Response::redirect('/admin/speakers');
    }

    public function edit(Request $request): Response
    {
        $speaker = $this->speakerService->find((int) $request->param('id'));
        if (!$speaker) {
            return Response::redirect('/admin/speakers');
        }

        return $this->view('admin/speakers/edit', [
            'speaker'    => $speaker,
            'pageTitle'  => 'Edit Speaker',
            'activePage' => 'speakers',
        ], 'admin/layouts/main');
    }

    public function update(Request $request): Response
    {
        $id   = (int) $request->param('id');
        $data = $request->only(['name', 'title', 'bio', 'photo_url', 'linkedin_url']);
        $this->speakerService->update($id, $data);
        Session::flash('success', 'Speaker updated.');
        return Response::redirect('/admin/speakers');
    }

    public function destroy(Request $request): Response
    {
        $this->speakerService->delete((int) $request->param('id'));
        Session::flash('success', 'Speaker removed.');
        return Response::redirect('/admin/speakers');
    }
}
