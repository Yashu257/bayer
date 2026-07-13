<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\SessionRepository;
use App\Helpers\TokenHelper;
use Core\Security\PasswordHasher;
use Core\Session\Session;
use Core\Exceptions\AuthException;
use Core\Logger\Logger;

/**
 * AuthService — handles user registration, login, remember-me, and logout.
 * No SQL. No HTTP. Pure business logic.
 */
class AuthService
{
    private readonly PasswordHasher     $hasher;
    private readonly UserRepository     $users;
    private readonly SessionRepository  $sessions;
    private readonly array              $authConfig;

    public function __construct()
    {
        $this->authConfig = require BASE_PATH . '/config/auth.php';
        $this->hasher     = new PasswordHasher($this->authConfig['password']);
        $this->users      = new UserRepository();
        $this->sessions   = new SessionRepository();
    }

    // -------------------------------------------------------------------------
    // Registration
    // -------------------------------------------------------------------------

    public function register(array $data): User
    {
        if ($this->users->emailExists($data['email'])) {
            throw new AuthException('An account with this email already exists.', 409);
        }

        $userId = $this->users->create([
            'first_name'         => $data['first_name'],
            'last_name'          => $data['last_name'],
            'email'              => $data['email'],
            'phone'              => $data['phone']     ?? null,
            'job_title'          => $data['job_title'] ?? null,
            'company'            => $data['company']   ?? null,
            'country'            => $data['country']   ?? null,
            'password_hash'      => $this->hasher->hash($data['password']),
            'verification_token' => TokenHelper::generateVerificationToken(),
            'status'             => 'pending',
        ]);

        $user = $this->users->findById($userId);
        if ($user === null) {
            throw new \RuntimeException('User creation failed.');
        }

        Logger::getInstance()->info('User registered.', ['user_id' => $userId, 'email' => $data['email']]);

        return $user;
    }

    // -------------------------------------------------------------------------
    // Login
    // -------------------------------------------------------------------------

    /**
     * Authenticate a user and start a server-side session.
     *
     * @return array{user: User, token: string}
     */
    public function login(string $email, string $password, bool $remember, string $ip, string $userAgent): array
    {
        $user = $this->users->findByEmail($email);

        // Constant-time comparison even when user not found (mitigates timing attacks)
        $hash = $user?->password_hash ?? '$argon2id$v=19$m=65536,t=4,p=2$fake$fake';
        if (!$this->hasher->verify($password, $hash) || $user === null) {
            Logger::getInstance()->warning('Failed login attempt.', ['email' => $email, 'ip' => $ip]);
            throw new AuthException('Invalid email or password.', 401);
        }

        if (!$user->canLogin()) {
            throw new AuthException('Your account is ' . $user->status . '. Please verify your email.', 403);
        }

        // Rehash if algorithm/options changed
        if ($this->hasher->needsRehash($user->password_hash)) {
            $this->users->updatePassword((int) $user->id, $this->hasher->hash($password));
        }

        $lifetime = $remember
            ? $this->authConfig['user']['remember_lifetime']
            : $this->authConfig['user']['session_lifetime'];

        $token = TokenHelper::generate(32);
        $this->sessions->createUserSession((int) $user->id, $token, $lifetime, $ip, $userAgent);
        $this->users->updateLastLogin((int) $user->id);

        // Store minimal identity in PHP session
        Session::set($this->authConfig['user']['session_key'], [
            'id'    => $user->id,
            'email' => $user->email,
            'token' => $token,
        ]);
        Session::regenerate(true);

        Logger::getInstance()->info('User logged in.', ['user_id' => $user->id, 'ip' => $ip]);

        return ['user' => $user, 'token' => $token];
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    public function logout(string $token): void
    {
        $this->sessions->deleteUserSession($token);
        Session::remove($this->authConfig['user']['session_key']);
        Session::regenerate(true);
    }

    // -------------------------------------------------------------------------
    // Remember Me — cookie resolution
    // -------------------------------------------------------------------------

    public function resolveRememberToken(string $token): ?User
    {
        $session = $this->sessions->findUserSession($token);
        if ($session === null) {
            return null;
        }

        $user = $this->users->findById((int) $session->user_id);
        if ($user === null || !$user->canLogin()) {
            $this->sessions->deleteUserSession($token);
            return null;
        }

        // Slide the expiry window
        $lifetime = $this->authConfig['user']['remember_lifetime'];
        $this->sessions->touchUserSession($token, $lifetime);

        return $user;
    }

    // -------------------------------------------------------------------------
    // Session resolution (called every request by AuthMiddleware)
    // -------------------------------------------------------------------------

    public function resolveSession(): ?User
    {
        $identity = Session::get($this->authConfig['user']['session_key']);
        if (!$identity || empty($identity['token'])) {
            return null;
        }

        $session = $this->sessions->findUserSession($identity['token']);
        if ($session === null) {
            Session::remove($this->authConfig['user']['session_key']);
            return null;
        }

        // Slide session window
        $this->sessions->touchUserSession($identity['token'], $this->authConfig['user']['session_lifetime']);

        return $this->users->findById((int) $identity['id']);
    }

    // -------------------------------------------------------------------------
    // Logout everywhere
    // -------------------------------------------------------------------------

    public function logoutAllDevices(int $userId): void
    {
        $this->sessions->deleteAllUserSessions($userId);
        Session::destroy();
    }
}
