<?php

declare(strict_types=1);

namespace Core\Security;

/**
 * PasswordHasher — wraps PHP password_hash / password_verify with Argon2id.
 */
final class PasswordHasher
{
    private array $options;
    private int   $algo;

    public function __construct(array $config)
    {
        $this->algo    = $config['algo']    ?? PASSWORD_ARGON2ID;
        $this->options = $config['algo_options'] ?? [
            'memory_cost' => 65536,
            'time_cost'   => 4,
            'threads'     => 2,
        ];
    }

    public function hash(string $plain): string
    {
        $hash = password_hash($plain, $this->algo, $this->options);

        if ($hash === false) {
            throw new \RuntimeException('Password hashing failed.');
        }

        return $hash;
    }

    public function verify(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, $this->algo, $this->options);
    }
}
