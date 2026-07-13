<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Services\LandingPageService;
use Core\Http\Request;
use Core\Http\Response;
use Core\Exceptions\HttpException;

class LandingPageController extends BaseController
{
    private readonly LandingPageService $service;

    public function __construct()
    {
        $this->service = new LandingPageService();
    }

    public function show(Request $request): Response
    {
        $slug = $request->getAttribute('slug');

        try {
            $data = $this->service->resolveForPublic((string) $slug);
        } catch (HttpException $e) {
            return Response::redirect('/404')->withStatus(302);
        }

        return $this->view(
            view:   'frontend.webcast.landing',
            data:   $data,
            layout: 'frontend/layouts/main',
            status: 200
        );
    }

    public function speakers(Request $request): Response
    {
        $slug = $request->getAttribute('slug');

        try {
            $data = $this->service->resolveForPublic((string) $slug);
        } catch (HttpException $e) {
            return Response::redirect('/404')->withStatus(302);
        }

        return $this->view('frontend.webcast.speakers', $data);
    }

    public function agenda(Request $request): Response
    {
        $slug = $request->getAttribute('slug');

        try {
            $data = $this->service->resolveForPublic((string) $slug);
        } catch (HttpException $e) {
            return Response::redirect('/404')->withStatus(302);
        }

        return $this->view('frontend.webcast.agenda', $data);
    }

    public function sponsors(Request $request): Response
    {
        $slug = $request->getAttribute('slug');

        try {
            $data = $this->service->resolveForPublic((string) $slug);
        } catch (HttpException $e) {
            return Response::redirect('/404')->withStatus(302);
        }

        return $this->view('frontend.webcast.sponsors', $data);
    }
}
