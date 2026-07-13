<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Admin;
use App\Repositories\AdminRepository;
use App\Repositories\SessionRepository;
use App\Helpers\TokenHelper;
use Core\Security\PasswordHasher;
use Core\Session\Session;
use Core\Exceptions\AuthException;
use Core\Logger\Logger;

/**
 * AdminAuthService — separate auth flow for back-office administrators.
 * Stricter: shorter session lifetime, IP recorded, no remember-me cookie.
 */
class AdminAuthService
{
    private readonly PasswordHasher    $hasher;
    private readonly AdminRepository   $admins;
    private readonly SessionRepository $sessions;
    private readonly array             $authConfig;

    public function __construct()
    {
        $this->authConfig = require BASE_PATH . '/config/auth.php';
        $this->hasher     = new PasswordHasher($this->authConfig['password']);
        $this->admins     = new AdminRepository();
        $this->sessions   = new SessionRepository();
    }

    /**
     * @return array{admin: Admin, token: string}
     */
    public function login(string $email, string $password, string $ip, string $userAgent): array
    {
        $admin = $this->admins->findByEmail($email);

        $hash = $admin?->password_hash ?? '$argon2id$v=19$m=65536,t=4,p=2$fake$fake';
        if (!$this->hasher->verify($password, $hash) || $admin === null) {
            Logger::getInstance()->warning('Admin login failed.', ['email' => $email, 'ip' => $ip]);
            throw new AuthException('Invalid credentials.', 401);
        }

        if (!$admin->isActive()) {
            throw new AuthException('This admin account is inactive.', 403);
        }

        if ($this->hasher->needsRehash($admin->password_hash)) {
            $this->admins->updatePassword((int) $admin->id, $this->hasher->hash($password));
        }

        $lifetime = $this->authConfig['admin']['session_lifetime'];
        $token    = TokenHelper::generate(40);

        $this->sessions->createAdminSession((int) $admin->id, $token, $lifetime, $ip, $userAgent);
        $this->admins->updateLastLogin((int) $admin->id, $ip);

        Session::set($this->authConfig['admin']['session_key'], [
            'id'    => $admin->id,
            'email' => $admin->email,
            'token' => $token,
            'role'  => $admin->role_slug,
        ]);
        Session::regenerate(true);

        Logger::getInstance()->info('Admin logged in.', ['admin_id' => $admin->id, 'ip' => $ip]);

        return ['admin' => $admin, 'token' => $token];
    }

    public function logout(string $token): void
    {
        $this->sessions->deleteAdminSession($token);
        Session::remove($this->authConfig['admin']['session_key']);
        Session::regenerate(true);
    }

    public function resolveSession(): ?Admin
    {
        $identity = Session::get($this->authConfig['admin']['session_key']);
        if (!$identity || empty($identity['token'])) {
            return null;
        }

        $session = $this->sessions->findAdminSession($identity['token']);
        if ($session === null) {
            Session::remove($this->authConfig['admin']['session_key']);
            return null;
        }

        $this->sessions->touchAdminSession($identity['token'], $this->authConfig['admin']['session_lifetime']);
        return $this->admins->findById((int) $identity['id']);
    }
}
