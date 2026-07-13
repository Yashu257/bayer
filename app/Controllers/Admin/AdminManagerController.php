<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\AdminAuthService;
use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class AdminManagerController extends BaseController
{
    public function index(Request $request): Response
    {
        $admins = Database::query(
            'SELECT id, name, email, role, created_at, last_login_at FROM admins ORDER BY name ASC'
        );

        return $this->view('admin/admin-manager/index', [
            'admins'     => $admins,
            'pageTitle'  => 'Admin Users',
            'activePage' => 'admin-users',
        ], 'admin/layouts/main');
    }

    public function create(Request $request): Response
    {
        return $this->view('admin/admin-manager/create', [
            'pageTitle'  => 'Add Admin',
            'activePage' => 'admin-users',
        ], 'admin/layouts/main');
    }

    public function store(Request $request): Response
    {
        $data     = $request->only(['name', 'email', 'role', 'password']);
        $password = $data['password'] ?? '';

        if (strlen($password) < 8) {
            Session::flash('error', 'Password must be at least 8 characters.');
            return Response::redirect('/admin/admins/create');
        }

        $exists = Database::queryOne('SELECT id FROM admins WHERE email = ?', [$data['email']]);
        if ($exists) {
            Session::flash('error', 'Email already registered.');
            return Response::redirect('/admin/admins/create');
        }

        Database::insert(
            'INSERT INTO admins (name, email, role, password, created_at) VALUES (?,?,?,?,NOW())',
            [$data['name'], $data['email'], $data['role'] ?? 'admin',
             password_hash($password, PASSWORD_BCRYPT)]
        );

        Session::flash('success', 'Admin account created.');
        return Response::redirect('/admin/admins');
    }

    public function edit(Request $request): Response
    {
        $admin = Database::queryOne(
            'SELECT id, name, email, role FROM admins WHERE id = ?', [(int) $request->param('id')]
        );

        if (!$admin) {
            return Response::redirect('/admin/admins');
        }

        return $this->view('admin/admin-manager/edit', [
            'admin'      => $admin,
            'pageTitle'  => 'Edit Admin',
            'activePage' => 'admin-users',
        ], 'admin/layouts/main');
    }

    public function update(Request $request): Response
    {
        $id   = (int) $request->param('id');
        $data = $request->only(['name', 'email', 'role']);

        Database::execute(
            'UPDATE admins SET name=?, email=?, role=?, updated_at=NOW() WHERE id=?',
            [$data['name'], $data['email'], $data['role'] ?? 'admin', $id]
        );

        $newPassword = $request->input('password', '');
        if (!empty($newPassword) && strlen($newPassword) >= 8) {
            Database::execute(
                'UPDATE admins SET password=? WHERE id=?',
                [password_hash($newPassword, PASSWORD_BCRYPT), $id]
            );
        }

        Session::flash('success', 'Admin updated.');
        return Response::redirect('/admin/admins');
    }

    public function destroy(Request $request): Response
    {
        $currentAdmin = Session::get('auth_admin');
        $targetId     = (int) $request->param('id');

        if ((int)($currentAdmin['id'] ?? 0) === $targetId) {
            Session::flash('error', 'You cannot delete your own account.');
            return Response::redirect('/admin/admins');
        }

        Database::execute('DELETE FROM admins WHERE id = ?', [$targetId]);
        Session::flash('success', 'Admin removed.');
        return Response::redirect('/admin/admins');
    }
}
