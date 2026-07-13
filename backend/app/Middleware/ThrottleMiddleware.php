<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Http\Response;

/**
 * ThrottleMiddleware — file-based rate limiter.
 * Usage: throttle:5,1  (5 hits per 1 minute per IP + route key)
 *
 * For production, replace the file cache with Redis/APCu.
 */
class ThrottleMiddleware
{
    private string $cacheDir;

    public function __construct()
    {
        $this->cacheDir = BASE_PATH . '/storage/cache/throttle';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0750, true);
        }
    }

    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        [$maxAttempts, $decayMinutes] = $this->parseParam($param ?? '60,1');

        $key      = $this->key($request);
        $cacheFile = $this->cacheDir . '/' . md5($key) . '.json';

        $data = $this->readCache($cacheFile);
        $now  = time();

        // Reset window if expired
        if ($data['reset_at'] <= $now) {
            $data = ['hits' => 0, 'reset_at' => $now + ($decayMinutes * 60)];
        }

        $data['hits']++;
        $this->writeCache($cacheFile, $data);

        if ($data['hits'] > $maxAttempts) {
            $retryAfter = $data['reset_at'] - $now;

            if ($request->isAjax() || $request->isJson()) {
                return Response::error('Too many requests. Please try again later.', 429)
                    ->withHeader('Retry-After', (string) $retryAfter)
                    ->withHeader('X-RateLimit-Limit', (string) $maxAttempts)
                    ->withHeader('X-RateLimit-Remaining', '0');
            }

            \Core\Session\Session::flash('error', 'Too many attempts. Please wait and try again.');
            return Response::redirect($_SERVER['HTTP_REFERER'] ?? '/')->withStatus(429);
        }

        return $next($request);
    }

    private function parseParam(string $param): array
    {
        $parts = explode(',', $param);
        return [(int) ($parts[0] ?? 60), (int) ($parts[1] ?? 1)];
    }

    private function key(Request $request): string
    {
        return $request->ip() . '|' . $request->uri();
    }

    private function readCache(string $file): array
    {
        if (!file_exists($file)) {
            return ['hits' => 0, 'reset_at' => 0];
        }
        $decoded = json_decode(file_get_contents($file) ?: '{}', true);
        return is_array($decoded) ? $decoded : ['hits' => 0, 'reset_at' => 0];
    }

    private function writeCache(string $file, array $data): void
    {
        file_put_contents($file, json_encode($data), LOCK_EX);
    }
}
