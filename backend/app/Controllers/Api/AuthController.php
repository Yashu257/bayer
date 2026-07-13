<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use Core\Http\Request;
use Core\Http\Response;
use Core\Validation\Validator;
use Core\Exceptions\ValidationException;
use Core\Exceptions\AuthException;
use App\Services\AuthService;

/**
 * API AuthController — stateless token issue / revoke for mobile / JS clients.
 */
class AuthController
{
    private readonly AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login(Request $request): Response
    {
        try {
            $body = $request->isJson() ? $request->json() : $request->all();

            $data = (new Validator($body, [
                'email'    => 'required|email|max:180',
                'password' => 'required|max:255',
            ]))->validate();

            $result = $this->authService->login(
                email:     $data['email'],
                password:  $data['password'],
                remember:  false,
                ip:        $request->ip(),
                userAgent: $request->userAgent(),
            );

            return Response::success([
                'token'      => $result['token'],
                'user'       => $result['user']->toArray(),
            ], 'Login successful.');

        } catch (ValidationException $e) {
            return Response::json(['success' => false, 'errors' => $e->errors()], 422);

        } catch (AuthException $e) {
            return Response::error($e->getMessage(), $e->getCode() ?: 401);
        }
    }

    public function logout(Request $request): Response
    {
        $token = $request->getAttribute('api_token');
        if ($token) {
            $this->authService->logout((string) $token);
        }
        return Response::success(null, 'Logged out.');
    }

    public function refresh(Request $request): Response
    {
        // Token sliding is handled by ApiAuthMiddleware on every request.
        // This endpoint simply confirms the token is still valid and returns user data.
        $user = $request->getAttribute('auth_user');
        return Response::success(['user' => $user?->toArray()], 'Token valid.');
    }

    public function me(Request $request): Response
    {
        $user = $request->getAttribute('auth_user');
        return Response::success(['user' => $user?->toArray()]);
    }
}
