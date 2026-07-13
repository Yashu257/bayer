<?php

declare(strict_types=1);

namespace Core\Http;

/**
 * Response — fluent HTTP response builder.
 */
class Response
{
    private int    $statusCode = 200;
    private array  $headers    = [];
    private string $body       = '';

    // --- Factory helpers -----------------------------------------------------

    public static function make(string $body = '', int $status = 200): self
    {
        $r = new self();
        return $r->withStatus($status)->withBody($body);
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return (new self())->withStatus($status)->withHeader('Location', $url);
    }

    public static function json(mixed $data, int $status = 200): self
    {
        return (new self())
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
            ->withBody((string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    public static function error(string $message, int $status = 400): self
    {
        return self::json(['success' => false, 'message' => $message], $status);
    }

    public static function success(mixed $data = null, string $message = 'OK'): self
    {
        return self::json(['success' => true, 'message' => $message, 'data' => $data]);
    }

    // --- Fluent setters ------------------------------------------------------

    public function withStatus(int $code): self
    {
        $clone             = clone $this;
        $clone->statusCode = $code;
        return $clone;
    }

    public function withHeader(string $name, string $value): self
    {
        $clone                  = clone $this;
        $clone->headers[$name]  = $value;
        return $clone;
    }

    public function withBody(string $body): self
    {
        $clone       = clone $this;
        $clone->body = $body;
        return $clone;
    }

    public function withCookie(
        string $name,
        string $value,
        int    $expires  = 0,
        string $path     = '/',
        string $domain   = '',
        bool   $secure   = false,
        bool   $httpOnly = true,
        string $sameSite = 'Lax'
    ): self {
        $clone = clone $this;
        $clone->headers['Set-Cookie'] = sprintf(
            '%s=%s; Expires=%s; Path=%s%s%s%s; SameSite=%s',
            urlencode($name),
            urlencode($value),
            $expires > 0 ? gmdate('D, d M Y H:i:s T', $expires) : '0',
            $path,
            $domain   ? "; Domain=$domain"    : '',
            $secure   ? '; Secure'            : '',
            $httpOnly ? '; HttpOnly'           : '',
            $sameSite
        );
        return $clone;
    }

    public function withoutCookie(string $name, string $path = '/'): self
    {
        return $this->withCookie($name, '', time() - 3600, $path);
    }

    // --- Send ----------------------------------------------------------------

    public function send(): never
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->body;
        exit;
    }
}
