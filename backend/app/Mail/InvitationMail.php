<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Invitation email — sent when an admin invites a specific HCP to register.
 *
 * Usage:
 *   (new InvitationMail($event, $recipient, $inviteToken))->queue();
 */
final class InvitationMail extends BaseMail
{
    /**
     * @param array $event    Event row from DB (id, title, starts_at, etc.)
     * @param array $recipient ['first_name', 'last_name', 'email', 'specialty']
     * @param string $token   Unique one-time registration token
     */
    public function __construct(
        private readonly array  $event,
        private readonly array  $recipient,
        private readonly string $token
    ) {
        parent::__construct();
    }

    protected function build(): void
    {
        $name = trim(($this->recipient['first_name'] ?? '') . ' ' . ($this->recipient['last_name'] ?? ''));
        $registerUrl = rtrim(env('APP_URL', 'https://pharmawebcast.com'), '/')
            . '/register?event=' . urlencode((string)($this->event['id'] ?? ''))
            . '&token=' . urlencode($this->token);

        $html = $this->renderTemplate('emails/invitation', [
            'recipientName' => $name,
            'recipientEmail'=> $this->recipient['email'] ?? '',
            'event'         => $this->event,
            'registerUrl'   => $registerUrl,
            'token'         => $this->token,
        ]);

        $this->message
            ->to($this->recipient['email'] ?? '', $name)
            ->subject('You\'re invited: ' . ($this->event['title'] ?? 'PharmaWebcast'))
            ->html($html)
            ->priority('high');
    }
}
