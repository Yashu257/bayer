<?php

declare(strict_types=1);

namespace Core\Mail;

use RuntimeException;

/**
 * Native PHP SMTP transport — no Composer dependencies.
 *
 * Supports:
 *   • Plain SMTP (port 25, no encryption)
 *   • STARTTLS   (port 587, encryption = 'tls')
 *   • SMTPS      (port 465, encryption = 'ssl')
 *   • AUTH LOGIN / AUTH PLAIN
 *   • Multipart/alternative (HTML + plain-text)
 */
final class SmtpTransport
{
    /** @var resource|null */
    private mixed $socket = null;

    private string $lastResponse = '';

    public function __construct(
        private readonly string $host,
        private readonly int    $port,
        private readonly string $encryption, // 'tls' | 'ssl' | ''
        private readonly string $username,
        private readonly string $password,
        private readonly int    $timeout = 30
    ) {}

    public function send(MailMessage $message): void
    {
        $this->connect();
        $this->ehlo();

        if ($this->encryption === 'tls') {
            $this->starttls();
            $this->ehlo(); // re-issue EHLO after upgrade
        }

        if ($this->username !== '') {
            $this->authenticate();
        }

        $fromAddress = $message->from['address'] ?? '';
        $this->command("MAIL FROM:<{$fromAddress}>", 250);

        foreach ($message->to as $r) {
            $this->command("RCPT TO:<{$r['address']}>", [250, 251]);
        }
        foreach ($message->cc as $r) {
            $this->command("RCPT TO:<{$r['address']}>", [250, 251]);
        }
        foreach ($message->bcc as $r) {
            $this->command("RCPT TO:<{$r['address']}>", [250, 251]);
        }

        $this->command('DATA', 354);
        $this->write($this->buildRaw($message));
        $this->command('.', 250);

        $this->command('QUIT', 221);
        $this->close();
    }

    // ── Private: connection ──────────────────────────────────────────

    private function connect(): void
    {
        $scheme = $this->encryption === 'ssl' ? 'ssl' : 'tcp';
        $dsn    = "{$scheme}://{$this->host}:{$this->port}";

        $errCode = 0;
        $errStr  = '';

        $socket = @stream_socket_client(
            $dsn,
            $errCode,
            $errStr,
            (float) $this->timeout,
            STREAM_CLIENT_CONNECT
        );

        if ($socket === false) {
            throw new RuntimeException(
                "SMTP: could not connect to {$this->host}:{$this->port} — {$errStr} ({$errCode})"
            );
        }

        stream_set_timeout($socket, $this->timeout);
        $this->socket = $socket;

        $this->read(220); // greeting
    }

    private function ehlo(): void
    {
        $this->command('EHLO ' . gethostname(), [250]);
    }

    private function starttls(): void
    {
        $this->command('STARTTLS', 220);

        $ok = stream_socket_enable_crypto(
            $this->socket,
            true,
            STREAM_CRYPTO_METHOD_TLS_CLIENT
        );

        if (!$ok) {
            throw new RuntimeException('SMTP: STARTTLS negotiation failed.');
        }
    }

    private function authenticate(): void
    {
        // Try AUTH LOGIN first, fallback to AUTH PLAIN
        $this->command('AUTH LOGIN', 334);
        $this->command(base64_encode($this->username), 334);
        $this->command(base64_encode($this->password), 235);
    }

    private function close(): void
    {
        if ($this->socket !== null) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    // ── Private: I/O ────────────────────────────────────────────────

    /**
     * @param int|int[] $expect
     */
    private function command(string $cmd, int|array $expect): string
    {
        $this->write($cmd . "\r\n");
        return $this->read($expect);
    }

    /**
     * @param int|int[] $expect
     */
    private function read(int|array $expect): string
    {
        $response = '';

        while (true) {
            $line = fgets($this->socket, 512);
            if ($line === false) {
                throw new RuntimeException('SMTP: connection lost while reading.');
            }
            $response .= $line;
            // Continue reading multi-line responses (e.g. "250-...")
            if (isset($line[3]) && $line[3] !== '-') {
                break;
            }
        }

        $this->lastResponse = $response;
        $code = (int) substr($response, 0, 3);
        $expected = (array) $expect;

        if (!in_array($code, $expected, true)) {
            throw new RuntimeException(
                "SMTP: expected " . implode('/', $expected) . ", got {$code}: " . trim($response)
            );
        }

        return $response;
    }

    private function write(string $data): void
    {
        if (fwrite($this->socket, $data) === false) {
            throw new RuntimeException('SMTP: failed to write to socket.');
        }
    }

    // ── Private: message building ────────────────────────────────────

    private function buildRaw(MailMessage $msg): string
    {
        $boundary = 'PWC_' . bin2hex(random_bytes(12));
        $date     = date('r');
        $msgId    = '<' . bin2hex(random_bytes(16)) . '@pharmawebcast.com>';

        $headers  = [];
        $headers[] = "Date: {$date}";
        $headers[] = "Message-ID: {$msgId}";
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'From: ' . MailMessage::formatAddress($msg->from);

        $toFormatted = array_map([MailMessage::class, 'formatAddress'], $msg->to);
        $headers[] = 'To: ' . implode(', ', $toFormatted);

        if (!empty($msg->cc)) {
            $ccFormatted = array_map([MailMessage::class, 'formatAddress'], $msg->cc);
            $headers[] = 'Cc: ' . implode(', ', $ccFormatted);
        }

        if (!empty($msg->replyTo)) {
            $rtFormatted = array_map([MailMessage::class, 'formatAddress'], $msg->replyTo);
            $headers[] = 'Reply-To: ' . implode(', ', $rtFormatted);
        }

        $headers[] = 'Subject: ' . MailMessage::encodeHeader($msg->subject);
        $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";

        $priorityHeaders = match ($msg->priority) {
            'high' => ["X-Priority: 1 (Highest)", "X-MSMail-Priority: High"],
            'low'  => ["X-Priority: 5 (Lowest)",  "X-MSMail-Priority: Low"],
            default => [],
        };
        $headers = array_merge($headers, $priorityHeaders);

        $textPart = wordwrap($msg->textBody ?: strip_tags($msg->htmlBody), 76, "\r\n", false);

        $body  = implode("\r\n", $headers) . "\r\n\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $body .= quoted_printable_encode($textPart) . "\r\n\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $body .= quoted_printable_encode($msg->htmlBody) . "\r\n\r\n";
        $body .= "--{$boundary}--\r\n";

        // Escape lines starting with a dot (RFC 2821 transparency)
        return preg_replace('/^\./', '..', $body);
    }
}
