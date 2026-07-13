<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Http\Request;
use Core\Http\Response;
use Core\Validation\Validator;
use Core\Exceptions\ValidationException;
use Core\Exceptions\AuthException;
use Core\Session\Session;
use App\Services\AdminAuthService;

class AuthController
{
    private readonly AdminAuthService $service;
    private readonly array            $authConfig;

    public function __construct()
    {
        $this->service    = new AdminAuthService();
        $this->authConfig = require BASE_PATH . '/config/auth.php';
    }

    public function showLogin(Request $request): Response
    {
        ob_start();
        include APP_PATH . '/Views/admin/auth/login.php';
        return Response::make(ob_get_clean());
    }

    public function login(Request $request): Response
    {
        try {
            $data = (new Validator($request->all(), [
                'email'    => 'required|email|max:180',
                'password' => 'required|max:255',
            ]))->validate();

            $result = $this->service->login(
                email:     $data['email'],
                password:  $data['password'],
                ip:        $request->ip(),
                userAgent: $request->userAgent(),
            );

            $intended = Session::getFlash('intended', '/admin/dashboard');
            return Response::redirect($intended);

        } catch (ValidationException $e) {
            Session::flash('errors', $e->errors());
            Session::flashOld($request->only('email'));
            return Response::redirect('/admin/login');

        } catch (AuthException $e) {
            Session::flash('error', $e->getMessage());
            Session::flashOld($request->only('email'));
            return Response::redirect('/admin/login');
        }
    }

    public function logout(Request $request): Response
    {
        $identity = Session::get($this->authConfig['admin']['session_key']);

        if ($identity && !empty($identity['token'])) {
            $this->service->logout($identity['token']);
        }

        Session::destroy();
        return Response::redirect('/admin/login');
    }

    // --- 2FA (placeholder — 2FA secret management lives in future AdminProfileService) ---

    public function show2FA(Request $request): Response
    {
        ob_start();
        include APP_PATH . '/Views/admin/auth/2fa.php';
        return Response::make(ob_get_clean());
    }

    public function verify2FA(Request $request): Response
    {
        // 2FA verification logic — implementation in future security module
        Session::flash('error', '2FA verification not yet implemented.');
        return Response::redirect('/admin/2fa');
    }
}
