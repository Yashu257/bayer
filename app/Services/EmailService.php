<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\InvitationMail;
use App\Mail\ReminderMail;
use App\Mail\ThankYouMail;
use Core\Logger\Logger;
use Throwable;

/**
 * High-level email façade for use inside controllers and other services.
 *
 * Every method queues the email (non-blocking) and returns the queue row ID.
 * Pass $sendNow = true to send synchronously (useful for password reset).
 *
 * All exceptions are caught and logged so a mail failure never breaks the
 * request; the return value is 0 on failure.
 */
final class EmailService
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger('email');
    }

    // ── 1. Invitation ────────────────────────────────────────────────

    /**
     * @param array  $event      Event DB row
     * @param array  $recipient  ['first_name', 'last_name', 'email', 'specialty']
     * @param string $token      One-time registration token
     */
    public function sendInvitation(array $event, array $recipient, string $token): int
    {
        return $this->dispatch(
            fn() => (new InvitationMail($event, $recipient, $token))->queue()
        );
    }

    // ── 2. Reminders ─────────────────────────────────────────────────

    /**
     * Queue both 24-hour and 1-hour reminders for an approved registrant.
     *
     * @param array $registration  Registration DB row
     * @param array $event         Event DB row (must have 'starts_at' column)
     * @return int[]  [queue_id_24h, queue_id_1h]
     */
    public function scheduleReminders(array $registration, array $event): array
    {
        $startsAt = strtotime($event['starts_at'] ?? 'now');
        $ids      = [];

        // 24-hour reminder
        $send24h = date('Y-m-d H:i:s', $startsAt - 86400);
        $ids[]   = $this->dispatch(
            fn() => (new ReminderMail($registration, $event, '24h'))->queue($send24h)
        );

        // 1-hour reminder
        $send1h = date('Y-m-d H:i:s', $startsAt - 3600);
        $ids[]  = $this->dispatch(
            fn() => (new ReminderMail($registration, $event, '1h'))->queue($send1h)
        );

        return $ids;
    }

    /**
     * Queue a single reminder for a custom window.
     *
     * @param string $window      '24h' | '1h' | '30min'
     * @param string $scheduledAt Any value accepted by strtotime()
     */
    public function sendReminder(
        array  $registration,
        array  $event,
        string $window      = '24h',
        string $scheduledAt = 'now'
    ): int {
        return $this->dispatch(
            fn() => (new ReminderMail($registration, $event, $window))->queue($scheduledAt)
        );
    }

    // ── 3. Thank-you / feedback ──────────────────────────────────────

    /**
     * @param string $delayAfterEvent  e.g. '+30 minutes' — how long after the event ends
     */
    public function sendThankYou(
        array  $registration,
        array  $event,
        string $delayAfterEvent = '+30 minutes'
    ): int {
        return $this->dispatch(
            fn() => (new ThankYouMail($registration, $event))->queue($delayAfterEvent)
        );
    }

    // ── 4. Bulk invitation (admin batch) ─────────────────────────────

    /**
     * Invite a list of recipients to the same event.
     * Returns count of successfully queued emails.
     *
     * @param array[] $recipients  Each: ['first_name', 'last_name', 'email', 'specialty']
     * @param array   $tokens      Keyed by email address
     */
    public function sendBulkInvitations(array $event, array $recipients, array $tokens): int
    {
        $queued = 0;
        foreach ($recipients as $recipient) {
            $email = $recipient['email'] ?? '';
            $token = $tokens[$email] ?? bin2hex(random_bytes(32));
            if ($this->dispatch(fn() => (new InvitationMail($event, $recipient, $token))->queue()) > 0) {
                $queued++;
            }
        }
        return $queued;
    }

    // ── Private ──────────────────────────────────────────────────────

    private function dispatch(callable $fn): int
    {
        try {
            return (int) $fn();
        } catch (Throwable $e) {
            $this->logger->error('EmailService dispatch failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }
}
