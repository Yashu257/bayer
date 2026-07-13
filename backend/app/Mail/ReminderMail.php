<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Event reminder email — typically queued to send 24 h and 1 h before start.
 *
 * Usage:
 *   (new ReminderMail($registration, $event, '24h'))->queue('+23 hours');
 *   (new ReminderMail($registration, $event, '1h'))->queue('+59 minutes');
 */
final class ReminderMail extends BaseMail
{
    /**
     * @param string $window  Human-readable label shown in the email: '24h' | '1h' | '30min'
     */
    public function __construct(
        private readonly array  $registration,
        private readonly array  $event,
        private readonly string $window = '24h'
    ) {
        parent::__construct();
    }

    protected function build(): void
    {
        $name     = trim(($this->registration['first_name'] ?? '') . ' ' . ($this->registration['last_name'] ?? ''));
        $baseUrl  = rtrim(env('APP_URL', 'https://pharmawebcast.com'), '/');
        $joinUrl  = $baseUrl . '/webcast/' . urlencode((string)($this->event['id'] ?? '')) . '/room';

        $windowLabel = match ($this->window) {
            '24h'   => '24 hours',
            '1h'    => '1 hour',
            '30min' => '30 minutes',
            default => $this->window,
        };

        $html = $this->renderTemplate('emails/reminder', [
            'recipientName' => $name,
            'registration'  => $this->registration,
            'event'         => $this->event,
            'joinUrl'       => $joinUrl,
            'windowLabel'   => $windowLabel,
        ]);

        $this->message
            ->to($this->registration['email'] ?? '', $name)
            ->subject("Reminder: {$windowLabel} until " . ($this->event['title'] ?? 'your webcast'))
            ->html($html)
            ->priority('high');
    }
}
