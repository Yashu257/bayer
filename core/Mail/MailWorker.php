<?php

declare(strict_types=1);

namespace Core\Mail;

use Throwable;

/**
 * Queue worker — run from a cron job or CLI:
 *
 *   php worker/mail-worker.php
 *
 * The worker picks up a batch of pending jobs, sends each one through the
 * real SMTP transport, and updates the queue row accordingly.
 *
 * It is intentionally stateless: each invocation processes one batch and exits.
 * Schedule it every minute via cron:
 *
 *   * * * * * php /var/www/pharma-webcast/worker/mail-worker.php >> /var/log/mail-worker.log 2>&1
 */
final class MailWorker
{
    private Mailer $mailer;
    private array  $config;

    public function __construct()
    {
        $this->mailer = new Mailer();
        $this->config = require BASE_PATH . '/config/mail.php';
    }

    public function run(): void
    {
        $batchSize = $this->config['queue']['batch_size'] ?? 20;
        $jobs      = MailQueue::fetchPending($batchSize);

        if (empty($jobs)) {
            $this->log('No pending jobs.');
            return;
        }

        $this->log(sprintf('Processing %d job(s)…', count($jobs)));

        foreach ($jobs as $job) {
            $this->process($job);
        }

        $this->log('Batch complete.');
    }

    // ── Private ──────────────────────────────────────────────────────

    private function process(array $job): void
    {
        $id = (int) $job['id'];

        MailQueue::markSending($id);

        try {
            $payload = json_decode($job['payload'], true, 512, JSON_THROW_ON_ERROR);
            $message = MailMessage::fromArray($payload);

            $this->mailer->send($message);

            MailQueue::markSent($id);
            $this->log("  [OK]  #{$id} → {$job['to_email']} | {$job['subject']}");

        } catch (Throwable $e) {
            $error = $e->getMessage();
            MailQueue::markFailed($id, $error);
            $this->log("  [ERR] #{$id} → {$job['to_email']} | {$error}");
        }
    }

    private function log(string $message): void
    {
        echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    }
}
