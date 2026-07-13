<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class BrandController extends BaseController
{
    public function index(Request $request): mixed
    {
        return $this->view('admin/brands/index', [
            'settings'  => [],
            'pageTitle' => 'Branding',
            'activePage'=> 'branding',
        ], 'admin/layouts/main');
    }

    public function update(Request $request): mixed
    {
        Session::flash('success', 'Branding updated.');
        return Response::redirect('/admin/brands');
    }
}
