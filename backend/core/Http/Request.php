<?php

declare(strict_types=1);

namespace Core\Http;

/**
 * Request — immutable wrapper around the current HTTP request.
 */
class Request
{
    private array $attributes = [];   // route-resolved data (event, user, etc.)

    private function __construct(
        private readonly string $method,
        private readonly string $uri,
        private readonly array  $query,
        private readonly array  $body,
        private readonly array  $files,
        private readonly array  $server,
        private readonly array  $headers,
        private readonly array  $cookies,
    ) {}

    public static function fromGlobals(): self
    {
        $uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';

        return new self(
            method:  strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            uri:     rtrim($uri, '/') ?: '/',
            query:   $_GET,
            body:    $_POST,
            files:   $_FILES,
            server:  $_SERVER,
            headers: self::parseHeaders(),
            cookies: $_COOKIE,
        );
    }

    // --- Accessors -----------------------------------------------------------

    public function method(): string  { return $this->method; }
    public function uri(): string     { return $this->uri; }
    public function isGet(): bool     { return $this->method === 'GET'; }
    public function isPost(): bool    { return $this->method === 'POST'; }
    public function isPut(): bool     { return $this->method === 'PUT'; }
    public function isDelete(): bool  { return $this->method === 'DELETE'; }

    public function isAjax(): bool
    {
        return ($this->headers['X-Requested-With'] ?? '') === 'XMLHttpRequest';
    }

    public function isJson(): bool
    {
        return str_contains($this->headers['Content-Type'] ?? '', 'application/json');
    }

    // --- Input ---------------------------------------------------------------

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function only(string ...$keys): array
    {
        $all = $this->all();
        return array_intersect_key($all, array_flip($keys));
    }

    public function has(string $key): bool
    {
        return isset($this->body[$key]) || isset($this->query[$key]);
    }

    /** Parse JSON body. Returns empty array if body is not valid JSON. */
    public function json(): array
    {
        if ($this->isJson()) {
            $raw = file_get_contents('php://input');
            return (array) (json_decode($raw ?: '', true) ?? []);
        }
        return [];
    }

    // --- Cookies & Headers ---------------------------------------------------

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $auth = $this->headers['Authorization'] ?? '';
        if (str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        return null;
    }

    // --- Server / IP ---------------------------------------------------------

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    // --- Route Attributes (set by middleware/router) -------------------------

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    // --- Helpers -------------------------------------------------------------

    private static function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name           = str_replace('_', '-', ucwords(strtolower(substr($key, 5)), '_'));
                $headers[$name] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                $name           = str_replace('_', '-', ucwords(strtolower($key), '_'));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }
}
