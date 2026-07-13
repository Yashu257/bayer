<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Post-event thank-you + feedback request email.
 *
 * Usage:
 *   (new ThankYouMail($registration, $event))->queue('+30 minutes');
 */
final class ThankYouMail extends BaseMail
{
    public function __construct(
        private readonly array $registration,
        private readonly array $event
    ) {
        parent::__construct();
    }

    protected function build(): void
    {
        $name        = trim(($this->registration['first_name'] ?? '') . ' ' . ($this->registration['last_name'] ?? ''));
        $baseUrl     = rtrim(env('APP_URL', 'https://pharmawebcast.com'), '/');
        $feedbackUrl = $baseUrl . '/feedback/' . urlencode((string)($this->event['id'] ?? ''))
            . '?aid=' . urlencode($this->registration['attendee_id'] ?? '');
        $cmeCertUrl  = $baseUrl . '/certificate/' . urlencode($this->registration['attendee_id'] ?? '');

        $html = $this->renderTemplate('emails/thank-you', [
            'recipientName' => $name,
            'registration'  => $this->registration,
            'event'         => $this->event,
            'feedbackUrl'   => $feedbackUrl,
            'cmeCertUrl'    => $cmeCertUrl,
        ]);

        $this->message
            ->to($this->registration['email'] ?? '', $name)
            ->subject('Thank you for attending — ' . ($this->event['title'] ?? 'PharmaWebcast'))
            ->html($html);
    }
}
