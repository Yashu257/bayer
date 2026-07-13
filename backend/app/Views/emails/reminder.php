<?php
/**
 * Email template: Event Reminder
 * Variables: $recipientName, $registration, $event, $joinUrl, $windowLabel
 */
$name       = htmlspecialchars($recipientName ?? 'Doctor', ENT_QUOTES, 'UTF-8');
$eventTitle = htmlspecialchars($event['title'] ?? 'PharmaWebcast', ENT_QUOTES, 'UTF-8');
$eventDate  = !empty($event['starts_at'])
    ? date('l, F j, Y \a\t g:i A T', strtotime($event['starts_at']))
    : '';
$joinUrl    = htmlspecialchars($joinUrl ?? '#', ENT_QUOTES, 'UTF-8');
$window     = htmlspecialchars($windowLabel ?? '24 hours', ENT_QUOTES, 'UTF-8');

$heroLabel = 'Event Reminder';
$heroTitle = "Starting in {$window}";

ob_start(); ?>
<p style="margin:0 0 20px;font-family:Arial,sans-serif;font-size:16px;color:#1e293b;line-height:1.7;">
    Dear <?= $name ?>,
</p>
<p style="margin:0 0 24px;font-family:Arial,sans-serif;font-size:15px;color:#475569;line-height:1.7;">
    This is a reminder that your live webcast begins in <strong><?= $window ?></strong>.
    Make sure you're ready to join!
</p>

<!-- Event info -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
    <tr>
        <td style="background:#f0f7ff;border:1px solid #bfdbfe;border-radius:10px;padding:24px;">
            <p style="margin:0 0 4px;font-family:Arial,sans-serif;font-size:18px;font-weight:700;color:#1e3a5f;"><?= $eventTitle ?></p>
            <?php if ($eventDate): ?>
            <p style="margin:0 0 16px;font-family:Arial,sans-serif;font-size:14px;color:#2563eb;">
                &#128197; <?= htmlspecialchars($eventDate, ENT_QUOTES, 'UTF-8') ?>
            </p>
            <?php endif; ?>
            <p style="margin:0 0 6px;font-family:Arial,sans-serif;font-size:13px;font-weight:700;color:#1e293b;">
                Your Attendee ID:
            </p>
            <p style="margin:0;font-family:'Courier New',monospace;font-size:18px;
                       font-weight:700;color:#1e3a5f;letter-spacing:2px;">
                <?= htmlspecialchars($registration['attendee_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </p>
        </td>
    </tr>
</table>

<!-- Checklist -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
    <tr>
        <td style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px 24px;">
            <p style="margin:0 0 12px;font-family:Arial,sans-serif;font-size:14px;font-weight:700;color:#1e293b;">
                &#9989; Quick Pre-Session Checklist
            </p>
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <?php foreach ([
                    'Stable internet connection (10 Mbps+ recommended)',
                    'Updated browser (Chrome, Firefox, Edge, or Safari)',
                    'Audio working — headphones recommended',
                    'Log in 5 minutes early to test your setup',
                ] as $item): ?>
                <tr>
                    <td width="24" valign="top" style="padding:4px 8px 4px 0;font-family:Arial,sans-serif;font-size:14px;color:#22c55e;">&#10003;</td>
                    <td style="padding:4px 0;font-family:Arial,sans-serif;font-size:13px;color:#475569;"><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>
</table>

<!-- CTA -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px;">
    <tr>
        <td style="border-radius:8px;background:#2563eb;">
            <a href="<?= $joinUrl ?>" target="_blank"
               style="display:inline-block;padding:14px 36px;font-family:Arial,sans-serif;
                      font-size:16px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:8px;">
                Join Webcast &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin:0;font-family:Arial,sans-serif;font-size:12px;color:#94a3b8;">
    If you can no longer attend, please notify us at
    <a href="mailto:support@pharmawebcast.com" style="color:#2563eb;">support@pharmawebcast.com</a>
    so we can release your spot.
</p>
<?php
$emailBody = ob_get_clean();
include __DIR__ . '/layouts/base.php';
