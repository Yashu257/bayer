<?php
/**
 * Email template: Invitation
 * Variables: $recipientName, $recipientEmail, $event, $registerUrl, $token
 */
$eventTitle  = htmlspecialchars($event['title']     ?? 'PharmaWebcast Event', ENT_QUOTES, 'UTF-8');
$eventDate   = !empty($event['starts_at'])
    ? date('l, F j, Y \a\t g:i A', strtotime($event['starts_at']))
    : '';
$eventDesc   = htmlspecialchars($event['description'] ?? '', ENT_QUOTES, 'UTF-8');
$name        = htmlspecialchars($recipientName ?? 'Doctor', ENT_QUOTES, 'UTF-8');
$url         = htmlspecialchars($registerUrl ?? '#', ENT_QUOTES, 'UTF-8');

$heroLabel = 'Personal Invitation';
$heroTitle = "You're invited, {$name}";

ob_start(); ?>
<p style="margin:0 0 20px;font-family:Arial,sans-serif;font-size:16px;
           color:#1e293b;line-height:1.7;">
    Dear <?= $name ?>,
</p>
<p style="margin:0 0 20px;font-family:Arial,sans-serif;font-size:15px;
           color:#475569;line-height:1.7;">
    We are delighted to invite you to attend a live pharmaceutical webcast exclusively
    for healthcare professionals:
</p>

<!-- Event card -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
       style="margin-bottom:28px;">
    <tr>
        <td style="background:#f0f7ff;border-left:4px solid #2563eb;
                   border-radius:0 8px 8px 0;padding:20px 24px;">
            <p style="margin:0 0 4px;font-family:Arial,sans-serif;font-size:18px;
                       font-weight:700;color:#1e3a5f;"><?= $eventTitle ?></p>
            <?php if ($eventDate): ?>
            <p style="margin:0 0 6px;font-family:Arial,sans-serif;font-size:13px;
                       color:#2563eb;">
                📅 <?= htmlspecialchars($eventDate, ENT_QUOTES, 'UTF-8') ?>
            </p>
            <?php endif; ?>
            <?php if ($eventDesc): ?>
            <p style="margin:8px 0 0;font-family:Arial,sans-serif;font-size:13px;
                       color:#64748b;line-height:1.6;"><?= $eventDesc ?></p>
            <?php endif; ?>
        </td>
    </tr>
</table>

<p style="margin:0 0 24px;font-family:Arial,sans-serif;font-size:15px;
           color:#475569;line-height:1.7;">
    Secure your place now — registration takes less than two minutes and your
    seat is reserved for you.
</p>

<!-- CTA button -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 32px;">
    <tr>
        <td style="border-radius:8px;background:#2563eb;">
            <a href="<?= $url ?>" target="_blank"
               style="display:inline-block;padding:14px 32px;font-family:Arial,sans-serif;
                      font-size:16px;font-weight:700;color:#ffffff;text-decoration:none;
                      border-radius:8px;">
                Register Now &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin:0 0 8px;font-family:Arial,sans-serif;font-size:12px;color:#94a3b8;">
    Or copy this link into your browser:
</p>
<p style="margin:0 0 24px;font-family:'Courier New',monospace;font-size:12px;
           color:#2563eb;word-break:break-all;"><?= $url ?></p>

<p style="margin:0;font-family:Arial,sans-serif;font-size:13px;color:#94a3b8;
           border-top:1px solid #e2e8f0;padding-top:20px;">
    This invitation is personal and non-transferable. The registration link is
    valid for 7 days. If you have already registered, you can disregard this email.
</p>
<?php
$emailBody = ob_get_clean();
include __DIR__ . '/layouts/base.php';
