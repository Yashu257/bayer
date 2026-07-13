<?php

declare(strict_types=1);

namespace App\Controllers\Api\Admin;

use App\Services\AdminAuthService;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class AuthController
{
    private readonly AdminAuthService $authService;

    public function __construct()
    {
        $this->authService = new AdminAuthService();
    }

    public function login(Request $request): Response
    {
        $body     = $request->isJson() ? $request->json() : $request->all();
        $email    = trim((string)($body['email']    ?? ''));
        $password = (string)($body['password'] ?? '');

        $admin = $this->authService->attempt($email, $password);

        if (!$admin) {
            return Response::json(['error' => 'Invalid credentials.'], 401);
        }

        return Response::json(['message' => 'Authenticated.', 'admin' => [
            'id'    => $admin['id'],
            'name'  => $admin['name'],
            'email' => $admin['email'],
            'role'  => $admin['role'],
        ]]);
    }

    public function logout(Request $request): Response
    {
        Session::destroy();
        return Response::json(['message' => 'Logged out.']);
    }
}
