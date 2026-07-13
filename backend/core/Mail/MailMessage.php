<?php

declare(strict_types=1);

namespace Core\Mail;

/**
 * Value object representing a single email ready to be sent or queued.
 */
final class MailMessage
{
    /** @var array<int, array{address: string, name: string}> */
    public array $to      = [];
    /** @var array<int, array{address: string, name: string}> */
    public array $cc      = [];
    /** @var array<int, array{address: string, name: string}> */
    public array $bcc     = [];
    /** @var array<int, array{address: string, name: string}> */
    public array $replyTo = [];

    public array  $from        = [];
    public string $subject     = '';
    public string $htmlBody    = '';
    public string $textBody    = '';
    public string $priority    = 'normal'; // 'high' | 'normal' | 'low'

    // ── Fluent builder ──────────────────────────────────────────────

    public function to(string $address, string $name = ''): static
    {
        $this->to[] = ['address' => $address, 'name' => $name];
        return $this;
    }

    public function cc(string $address, string $name = ''): static
    {
        $this->cc[] = ['address' => $address, 'name' => $name];
        return $this;
    }

    public function bcc(string $address, string $name = ''): static
    {
        $this->bcc[] = ['address' => $address, 'name' => $name];
        return $this;
    }

    public function from(string $address, string $name = ''): static
    {
        $this->from = ['address' => $address, 'name' => $name];
        return $this;
    }

    public function replyTo(string $address, string $name = ''): static
    {
        $this->replyTo[] = ['address' => $address, 'name' => $name];
        return $this;
    }

    public function subject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function html(string $html): static
    {
        $this->htmlBody = $html;
        return $this;
    }

    public function text(string $text): static
    {
        $this->textBody = $text;
        return $this;
    }

    public function priority(string $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    // ── Helpers ─────────────────────────────────────────────────────

    public function primaryTo(): array
    {
        return $this->to[0] ?? [];
    }

    public function toArray(): array
    {
        return [
            'to'       => $this->to,
            'cc'       => $this->cc,
            'bcc'      => $this->bcc,
            'reply_to' => $this->replyTo,
            'from'     => $this->from,
            'subject'  => $this->subject,
            'html'     => $this->htmlBody,
            'text'     => $this->textBody,
            'priority' => $this->priority,
        ];
    }

    public static function fromArray(array $data): static
    {
        $msg           = new static();
        $msg->to       = $data['to']       ?? [];
        $msg->cc       = $data['cc']       ?? [];
        $msg->bcc      = $data['bcc']      ?? [];
        $msg->replyTo  = $data['reply_to'] ?? [];
        $msg->from     = $data['from']     ?? [];
        $msg->subject  = $data['subject']  ?? '';
        $msg->htmlBody = $data['html']     ?? '';
        $msg->textBody = $data['text']     ?? '';
        $msg->priority = $data['priority'] ?? 'normal';
        return $msg;
    }

    // ── RFC 2822 helpers ─────────────────────────────────────────────

    public static function encodeHeader(string $value): string
    {
        if (mb_detect_encoding($value, 'ASCII', true)) {
            return $value;
        }
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    public static function formatAddress(array $addr): string
    {
        if (empty($addr['name'])) {
            return '<' . $addr['address'] . '>';
        }
        return static::encodeHeader($addr['name']) . ' <' . $addr['address'] . '>';
    }
}
