<?php

declare(strict_types=1);

namespace Core\Mailer;

use Core\Logger\Logger;

/**
 * Mailer — thin SMTP abstraction using PHP's mail() or an SMTP socket.
 *
 * Architecture note:
 *   This class is the ONLY place that sends email.
 *   All email content is built in view templates under app/Views/emails/.
 *   Listeners call Mailer::send(); they never build headers or call mail() themselves.
 *
 * Production upgrade path:
 *   Replace the mail() call with a proper SMTP library (e.g. PHPMailer, Symfony Mailer)
 *   without touching any listener, service, or controller.
 */
class Mailer
{
    private readonly array $config;

    public function __construct()
    {
        $this->config = [
            'from_address' => $_ENV['MAIL_FROM']      ?? 'noreply@pharmawebcast.com',
            'from_name'    => $_ENV['MAIL_FROM_NAME'] ?? 'PharmaWebcast',
            'host'         => $_ENV['MAIL_HOST']      ?? '',
            'port'         => (int) ($_ENV['MAIL_PORT']     ?? 587),
            'username'     => $_ENV['MAIL_USERNAME']  ?? '',
            'password'     => $_ENV['MAIL_PASSWORD']  ?? '',
        ];
    }

    /**
     * Send a plain-text + HTML email.
     *
     * @param string      $to       Recipient email address
     * @param string      $subject  Email subject line
     * @param string      $html     HTML body (rendered from a view template)
     * @param string|null $text     Plain-text fallback (auto-stripped from HTML if null)
     * @param array       $extra    Additional To headers [['name'=>'...','email'=>'...']]
     */
    public function send(
        string  $to,
        string  $subject,
        string  $html,
        ?string $text  = null,
        array   $extra = [],
    ): bool {
        $text  ??= $this->stripToText($html);
        $from    = $this->config['from_name']
            ? '"' . addslashes($this->config['from_name']) . '" <' . $this->config['from_address'] . '>'
            : $this->config['from_address'];

        $boundary = 'PHARMA_' . bin2hex(random_bytes(8));

        $headers  = implode("\r\n", [
            "MIME-Version: 1.0",
            "Content-Type: multipart/alternative; boundary=\"$boundary\"",
            "From: $from",
            "Reply-To: " . $this->config['from_address'],
            "X-Mailer: PharmaWebcast/1.0",
        ]);

        $body = "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
            . quoted_printable_encode($text)
            . "\r\n\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
            . quoted_printable_encode($html)
            . "\r\n\r\n"
            . "--{$boundary}--";

        try {
            $result = mail($to, $subject, $body, $headers);

            Logger::getInstance()->info('Email dispatched.', [
                'to'      => $to,
                'subject' => $subject,
                'sent'    => $result,
            ]);

            return $result;

        } catch (\Throwable $e) {
            Logger::getInstance()->error('Email send failed.', [
                'to'    => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Render an email view template and return the HTML string.
     *
     * @param string $view  Dot-notation path under app/Views/emails/  e.g. 'registration-confirmation'
     * @param array  $data  Variables available inside the template
     */
    public function render(string $view, array $data = []): string
    {
        $file = APP_PATH . '/Views/emails/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($file)) {
            throw new \RuntimeException("Email template not found: $file");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $file;
        return ob_get_clean() ?: '';
    }

    private function stripToText(string $html): string
    {
        $text = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>'], "\n", $html));
        return html_entity_decode(preg_replace('/[ \t]+/', ' ', $text) ?? $text, ENT_QUOTES, 'UTF-8');
    }
}
