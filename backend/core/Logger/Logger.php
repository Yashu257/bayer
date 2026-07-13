<?php

declare(strict_types=1);

namespace Core\Logger;

/**
 * Logger — PSR-3-inspired file logger. Singleton.
 * Writes JSON lines to storage/logs/app-YYYY-MM-DD.log
 */
final class Logger
{
    private static ?self $instance = null;
    private string $logDir;
    private string $minLevel;

    private const LEVELS = ['debug' => 0, 'info' => 1, 'notice' => 2, 'warning' => 3, 'error' => 4, 'critical' => 5];

    private function __construct(string $minLevel = 'debug')
    {
        $this->minLevel = $minLevel;
        $this->logDir = BASE_PATH . '/storage/logs';
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            $level          = $_ENV['LOG_LEVEL'] ?? 'debug';
            self::$instance = new self($level);
        }
        return self::$instance;
    }

    public function debug(string $message, array $context = []): void   { $this->log('debug',    $message, $context); }
    public function info(string $message, array $context = []): void    { $this->log('info',     $message, $context); }
    public function notice(string $message, array $context = []): void  { $this->log('notice',   $message, $context); }
    public function warning(string $message, array $context = []): void { $this->log('warning',  $message, $context); }
    public function error(string $message, array $context = []): void   { $this->log('error',    $message, $context); }
    public function critical(string $message, array $context = []): void{ $this->log('critical', $message, $context); }

    private function log(string $level, string $message, array $context): void
    {
        if (self::LEVELS[$level] < self::LEVELS[$this->minLevel]) {
            return;
        }

        $entry = json_encode([
            'ts'      => date('Y-m-d H:i:s'),
            'level'   => strtoupper($level),
            'message' => $message,
            'context' => $context,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $file = $this->logDir . '/app-' . date('Y-m-d') . '.log';
        file_put_contents($file, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
