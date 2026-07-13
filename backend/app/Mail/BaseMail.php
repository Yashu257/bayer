<?php

declare(strict_types=1);

namespace App\Mail;

use Core\Mail\Mailer;
use Core\Mail\MailMessage;

/**
 * Base class for all Mailable objects.
 *
 * Concrete mailables implement build() to configure the MailMessage,
 * then call send() or queue() on themselves.
 *
 * Example:
 *   (new InvitationMail($event, $recipient))->send();
 *   (new ReminderMail($event, $recipient))->queue('+10 minutes');
 */
abstract class BaseMail
{
    protected MailMessage $message;
    protected Mailer      $mailer;

    public function __construct()
    {
        $this->message = new MailMessage();
        $this->mailer  = new Mailer();
    }

    /**
     * Configure $this->message — called automatically before send/queue.
     */
    abstract protected function build(): void;

    /**
     * Send immediately (synchronous, blocks until SMTP responds).
     */
    public function send(): void
    {
        $this->build();
        $this->mailer->send($this->message);
    }

    /**
     * Push onto the database queue for async delivery.
     *
     * @param string $scheduledAt  e.g. 'now', '+5 minutes', '2024-07-15 09:00:00'
     * @return int  Queue row ID
     */
    public function queue(string $scheduledAt = 'now'): int
    {
        $this->build();
        return $this->mailer->queue($this->message, $scheduledAt);
    }

    // ── Helpers used by subclasses ───────────────────────────────────

    protected function renderTemplate(string $template, array $data = []): string
    {
        return $this->mailer->render($template, $data);
    }
}
