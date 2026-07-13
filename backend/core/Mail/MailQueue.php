<?php

declare(strict_types=1);

namespace Core\Mail;

use Core\Database\DB;

/**
 * Persists outbound emails to the `email_queue` table and retrieves
 * jobs for the worker to process.
 *
 * Schema: see database/migrations/create_email_queue.sql
 */
final class MailQueue
{
    // ── Producer ─────────────────────────────────────────────────────

    /**
     * Push a MailMessage onto the queue.
     *
     * @param string $scheduledAt  Any value accepted by strtotime() — 'now', '+5 minutes', etc.
     * @return int  The new queue row ID
     */
    public static function push(MailMessage $message, string $scheduledAt = 'now'): int
    {
        $primary     = $message->primaryTo();
        $ts          = strtotime($scheduledAt);
        $scheduledAt = date('Y-m-d H:i:s', $ts === false ? time() : $ts);
        $config      = require BASE_PATH . '/config/mail.php';
        $maxAttempts = $config['queue']['max_attempts'] ?? 3;

        return DB::insert(
            'INSERT INTO email_queue
                (to_email, to_name, subject, html_body, text_body, payload,
                 status, attempts, max_attempts, scheduled_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, \'pending\', 0, ?, ?, NOW())',
            [
                $primary['address']  ?? '',
                $primary['name']     ?? '',
                $message->subject,
                $message->htmlBody,
                $message->textBody,
                json_encode($message->toArray()),
                $maxAttempts,
                $scheduledAt,
            ]
        );
    }

    // ── Consumer (used by MailWorker) ─────────────────────────────────

    /**
     * Fetch the next N pending jobs that are due, locking them for processing.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function fetchPending(int $limit = 20): array
    {
        return DB::query(
            'SELECT * FROM email_queue
              WHERE status = \'pending\'
                AND scheduled_at <= NOW()
                AND attempts < max_attempts
              ORDER BY scheduled_at ASC
              LIMIT ?',
            [$limit]
        );
    }

    public static function markSending(int $id): void
    {
        DB::execute(
            'UPDATE email_queue SET status = \'sending\', attempts = attempts + 1, updated_at = NOW() WHERE id = ?',
            [$id]
        );
    }

    public static function markSent(int $id): void
    {
        DB::execute(
            'UPDATE email_queue SET status = \'sent\', sent_at = NOW(), updated_at = NOW() WHERE id = ?',
            [$id]
        );
    }

    public static function markFailed(int $id, string $error): void
    {
        $config      = require BASE_PATH . '/config/mail.php';
        $retryDelay  = $config['queue']['retry_delay'] ?? 300;

        // If more attempts remain, reschedule; otherwise mark permanently failed
        DB::execute(
            'UPDATE email_queue
                SET status        = CASE WHEN attempts >= max_attempts THEN \'failed\' ELSE \'pending\' END,
                    error_message = ?,
                    failed_at     = NOW(),
                    scheduled_at  = CASE WHEN attempts >= max_attempts THEN scheduled_at
                                         ELSE DATE_ADD(NOW(), INTERVAL ? SECOND) END,
                    updated_at    = NOW()
              WHERE id = ?',
            [$error, $retryDelay, $id]
        );
    }

    // ── Stats ─────────────────────────────────────────────────────────

    public static function counts(): array
    {
        return DB::query(
            'SELECT status, COUNT(*) AS total FROM email_queue GROUP BY status'
        );
    }
}
