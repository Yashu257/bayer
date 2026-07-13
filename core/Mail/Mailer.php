<?php

declare(strict_types=1);

namespace Core\Mail;

use RuntimeException;

/**
 * Central mailer: renders PHP view templates, resolves transport from config,
 * and either sends immediately or delegates to MailQueue.
 *
 * Usage (immediate):
 *   $mailer = new Mailer();
 *   $mailer->send($message);
 *
 * Usage (queued):
 *   $mailer->queue($message);           // scheduled_at = now
 *   $mailer->queue($message, '+5 minutes');
 */
final class Mailer
{
    private array  $config;
    private string $driver;

    public function __construct()
    {
        $this->config = require BASE_PATH . '/config/mail.php';
        $this->driver = $this->config['driver'] ?? 'smtp';
    }

    // ── Public API ───────────────────────────────────────────────────

    public function send(MailMessage $message): void
    {
        $this->applyDefaults($message);

        match ($this->driver) {
            'smtp'  => $this->sendViaSmtp($message),
            'log'   => $this->sendViaLog($message),
            'null'  => null, // discard
            default => throw new RuntimeException("Unknown mail driver: {$this->driver}"),
        };
    }

    public function queue(MailMessage $message, string $scheduledAt = 'now'): int
    {
        $this->applyDefaults($message);
        return MailQueue::push($message, $scheduledAt);
    }

    /**
     * Render a PHP view template into an HTML string.
     *
     * @param string $template  e.g. 'emails/invitation'
     * @param array  $data      variables available inside the template
     */
    public function render(string $template, array $data = []): string
    {
        $path = BASE_PATH . '/app/Views/' . ltrim($template, '/') . '.php';

        if (!file_exists($path)) {
            throw new RuntimeException("Mail template not found: {$path}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $path;
        return ob_get_clean() ?: '';
    }

    // ── Private ──────────────────────────────────────────────────────

    private function applyDefaults(MailMessage $message): void
    {
        if (empty($message->from)) {
            $message->from(
                $this->config['from']['address'],
                $this->config['from']['name']
            );
        }

        if (empty($message->replyTo) && !empty($this->config['reply_to']['address'])) {
            $message->replyTo(
                $this->config['reply_to']['address'],
                $this->config['reply_to']['name'] ?? ''
            );
        }
    }

    private function sendViaSmtp(MailMessage $message): void
    {
        $transport = new SmtpTransport(
            host:       $this->config['host'],
            port:       $this->config['port'],
            encryption: $this->config['encryption'],
            username:   $this->config['username'],
            password:   $this->config['password'],
            timeout:    $this->config['timeout'] ?? 30
        );

        $transport->send($message);
    }

    private function sendViaLog(MailMessage $message): void
    {
        $path = $this->config['log_path'] ?? (BASE_PATH . '/storage/logs/mail.log');
        $dir  = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $recipient = $message->primaryTo()['address'] ?? 'unknown';
        $entry     = sprintf(
            "[%s] TO:%s | SUBJECT:%s\n%s\n%s\n",
            date('Y-m-d H:i:s'),
            $recipient,
            $message->subject,
            str_repeat('-', 60),
            $message->htmlBody
        );

        file_put_contents($path, $entry, FILE_APPEND | LOCK_EX);
    }
}
