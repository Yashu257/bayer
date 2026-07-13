<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml"
      xmlns:v="urn:schemas-microsoft-com:vml"
      xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no,date=no,address=no,email=no,url=no">
    <title><?= htmlspecialchars($emailTitle ?? 'PharmaWebcast', ENT_QUOTES, 'UTF-8') ?></title>
    <!--[if mso]>
    <noscript>
        <xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml>
    </noscript>
    <![endif]-->
    <style>
        /* Reset */
        * { box-sizing: border-box; }
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; background-color: #f0f4f8; }
        a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }
        /* Dark mode */
        @media (prefers-color-scheme: dark) {
            body, .email-bg { background-color: #0f172a !important; }
            .card { background-color: #1e293b !important; }
            .card-body-text, p, li { color: #cbd5e1 !important; }
            .email-footer-text { color: #64748b !important; }
        }
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-wrapper { width: 100% !important; max-width: 100% !important; }
            .card { border-radius: 0 !important; }
            .px-mobile { padding-left: 20px !important; padding-right: 20px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
       class="email-bg" style="background-color:#f0f4f8;">
<tr><td align="center" style="padding: 40px 16px 0;">

    <!-- ═══ HEADER BRAND STRIP ═══════════════════════════════════ -->
    <table role="presentation" class="email-wrapper" width="600" cellpadding="0" cellspacing="0"
           border="0" style="max-width:600px;width:100%;">
        <tr>
            <td align="center" style="padding-bottom:24px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="background:#1e3a5f;border-radius:10px;padding:10px 20px;">
                            <span style="font-family:Arial,sans-serif;font-size:20px;font-weight:700;
                                         color:#ffffff;letter-spacing:.5px;">
                                📡 PharmaWebcast
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ═══ CARD ══════════════════════════════════════════════════ -->
    <table role="presentation" class="email-wrapper card" width="600" cellpadding="0" cellspacing="0"
           border="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;
                             box-shadow:0 4px 24px rgba(0,0,0,.08);">
        <tr>
            <!-- Hero colour bar -->
            <td style="background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 100%);
                       border-radius:12px 12px 0 0;padding:32px 40px;">
                <p style="margin:0;font-family:Arial,sans-serif;font-size:13px;
                           color:rgba(255,255,255,.7);text-transform:uppercase;
                           letter-spacing:1.5px;"><?= htmlspecialchars($heroLabel ?? 'PharmaWebcast', ENT_QUOTES, 'UTF-8') ?></p>
                <h1 style="margin:8px 0 0;font-family:Arial,sans-serif;font-size:26px;
                            font-weight:700;color:#ffffff;line-height:1.3;">
                    <?= htmlspecialchars($heroTitle ?? '', ENT_QUOTES, 'UTF-8') ?>
                </h1>
            </td>
        </tr>
        <tr>
            <!-- Body -->
            <td class="px-mobile" style="padding:36px 40px;" class="card-body-text">
                <?= $emailBody ?? '' ?>
            </td>
        </tr>
        <tr>
            <!-- Footer -->
            <td style="background:#f8fafc;border-radius:0 0 12px 12px;
                       border-top:1px solid #e2e8f0;padding:24px 40px;"
                class="email-footer-text">
                <p style="margin:0 0 6px;font-family:Arial,sans-serif;font-size:12px;
                           color:#64748b;line-height:1.6;">
                    You are receiving this email because you registered for a PharmaWebcast event.
                    This message is intended only for healthcare professionals.
                </p>
                <p style="margin:0;font-family:Arial,sans-serif;font-size:12px;color:#94a3b8;">
                    © <?= date('Y') ?> PharmaWebcast. All rights reserved.
                </p>
            </td>
        </tr>
    </table>

    <!-- ═══ SPACER ════════════════════════════════════════════════ -->
    <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
           style="max-width:600px;width:100%;">
        <tr><td style="height:40px;">&nbsp;</td></tr>
    </table>

</td></tr>
</table>
</body>
</html>
