<?php
/**
 * Email template: Post-event Thank You + Feedback Request
 * Variables: $recipientName, $registration, $event, $feedbackUrl, $cmeCertUrl
 */
$name        = htmlspecialchars($recipientName ?? 'Doctor', ENT_QUOTES, 'UTF-8');
$eventTitle  = htmlspecialchars($event['title'] ?? 'PharmaWebcast', ENT_QUOTES, 'UTF-8');
$feedbackUrl = htmlspecialchars($feedbackUrl ?? '#', ENT_QUOTES, 'UTF-8');
$cmeCertUrl  = htmlspecialchars($cmeCertUrl  ?? '#', ENT_QUOTES, 'UTF-8');
$watchSeconds = (int)($registration['watch_seconds'] ?? 0);
$watchDisplay = $watchSeconds >= 60
    ? round($watchSeconds / 60) . ' min'
    : $watchSeconds . ' sec';

$heroLabel = 'Thank You';
$heroTitle = "Thank you for attending, {$name}!";

ob_start(); ?>
<p style="margin:0 0 20px;font-family:Arial,sans-serif;font-size:16px;color:#1e293b;line-height:1.7;">
    Dear <?= $name ?>,
</p>
<p style="margin:0 0 24px;font-family:Arial,sans-serif;font-size:15px;color:#475569;line-height:1.7;">
    Thank you for joining <strong><?= $eventTitle ?></strong>.
    We hope you found the content valuable and insightful for your practice.
</p>

<!-- Attendance summary -->
<?php if ($watchSeconds > 0): ?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
    <tr>
        <td style="background:#f0f7ff;border:1px solid #bfdbfe;border-radius:10px;padding:20px 24px;">
            <p style="margin:0 0 12px;font-family:Arial,sans-serif;font-size:13px;font-weight:700;color:#1e3a5f;">
                Your Session Summary
            </p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td width="50%">
                        <p style="margin:0 0 2px;font-family:Arial,sans-serif;font-size:11px;color:#94a3b8;text-transform:uppercase;">Watch Time</p>
                        <p style="margin:0;font-family:Arial,sans-serif;font-size:18px;font-weight:700;color:#1e3a5f;"><?= $watchDisplay ?></p>
                    </td>
                    <td width="50%">
                        <p style="margin:0 0 2px;font-family:Arial,sans-serif;font-size:11px;color:#94a3b8;text-transform:uppercase;">Attendee ID</p>
                        <p style="margin:0;font-family:'Courier New',monospace;font-size:14px;font-weight:700;color:#1e3a5f;">
                            <?= htmlspecialchars($registration['attendee_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<?php endif; ?>

<!-- Action cards -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
    <tr>
        <td width="48%" valign="top" style="padding-right:8px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="background:#f0f7ff;border:1px solid #bfdbfe;border-radius:10px;padding:20px;text-align:center;">
                        <p style="margin:0 0 4px;font-size:28px;">&#128196;</p>
                        <p style="margin:0 0 8px;font-family:Arial,sans-serif;font-size:14px;font-weight:700;color:#1e3a5f;">CME Certificate</p>
                        <p style="margin:0 0 16px;font-family:Arial,sans-serif;font-size:12px;color:#64748b;line-height:1.5;">Download your continuing medical education certificate</p>
                        <a href="<?= $cmeCertUrl ?>" target="_blank"
                           style="display:inline-block;padding:10px 20px;font-family:Arial,sans-serif;
                                  font-size:13px;font-weight:700;color:#ffffff;text-decoration:none;
                                  background:#2563eb;border-radius:6px;">
                            Download PDF
                        </a>
                    </td>
                </tr>
            </table>
        </td>
        <td width="4%">&nbsp;</td>
        <td width="48%" valign="top" style="padding-left:8px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:20px;text-align:center;">
                        <p style="margin:0 0 4px;font-size:28px;">&#11088;</p>
                        <p style="margin:0 0 8px;font-family:Arial,sans-serif;font-size:14px;font-weight:700;color:#9a3412;">Share Feedback</p>
                        <p style="margin:0 0 16px;font-family:Arial,sans-serif;font-size:12px;color:#64748b;line-height:1.5;">Help us improve future events with your valuable feedback</p>
                        <a href="<?= $feedbackUrl ?>" target="_blank"
                           style="display:inline-block;padding:10px 20px;font-family:Arial,sans-serif;
                                  font-size:13px;font-weight:700;color:#ffffff;text-decoration:none;
                                  background:#f59e0b;border-radius:6px;">
                            Give Feedback
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin:0 0 16px;font-family:Arial,sans-serif;font-size:15px;color:#475569;line-height:1.7;">
    We look forward to welcoming you at our future educational events.
    Stay up to date with our upcoming webcasts by visiting your account dashboard.
</p>

<p style="margin:0;font-family:Arial,sans-serif;font-size:13px;color:#94a3b8;">
    Questions? Contact us at
    <a href="mailto:support@pharmawebcast.com" style="color:#2563eb;">support@pharmawebcast.com</a>
</p>
<?php
$emailBody = ob_get_clean();
include __DIR__ . '/layouts/base.php';
