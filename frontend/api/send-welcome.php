<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false,'error'=>'Method not allowed']); exit; }

$body = json_decode(file_get_contents('php://input'), true);
$name  = isset($body['name'])  ? trim($body['name'])  : '';
$email = isset($body['email']) ? trim($body['email']) : '';

if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok'=>false,'error'=>'Invalid name or email']); exit;
}

// ── SMTP config ──
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_user = 'support@coact.co.in';
$smtp_pass = 'llzqtrygnymzagzg';
$from_name = 'PharmaWebcast';

$subject = 'Welcome to PharmaWebcast – You\'re Registered!';
$message = "Dear {$name},\r\n\r\n"
    . "Thank you for registering for PharmaWebcast.\r\n\r\n"
    . "You can now log in to the live webcast using your registered name and email address.\r\n\r\n"
    . "We look forward to seeing you at the event!\r\n\r\n"
    . "Warm regards,\r\nThe PharmaWebcast Team\r\nsupport@coact.co.in";

function smtp_send($host, $port, $user, $pass, $from, $from_name, $to, $subject, $body) {
    $fp = fsockopen('ssl://'.$host, 465, $errno, $errstr, 15);
    if (!$fp) {
        // fallback to TLS on 587
        $fp = stream_socket_client('tcp://'.$host.':'.$port, $errno, $errstr, 15);
        if (!$fp) return ['ok'=>false,'error'=>"Connect failed: $errstr ($errno)"];
        smtp_read($fp);
        smtp_cmd($fp, "EHLO localhost");
        stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        smtp_cmd($fp, "EHLO localhost");
    } else {
        smtp_read($fp);
        smtp_cmd($fp, "EHLO localhost");
    }
    $r = smtp_cmd($fp, "AUTH LOGIN");
    $r = smtp_cmd($fp, base64_encode($user));
    $r = smtp_cmd($fp, base64_encode($pass));
    if (strpos($r, '235') === false) { fclose($fp); return ['ok'=>false,'error'=>'Auth failed: '.$r]; }

    smtp_cmd($fp, "MAIL FROM:<{$user}>");
    smtp_cmd($fp, "RCPT TO:<{$to}>");
    smtp_cmd($fp, "DATA");

    $headers  = "From: {$from_name} <{$user}>\r\n";
    $headers .= "To: {$to}\r\n";
    $headers .= "Subject: {$subject}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Date: ".date('r')."\r\n";

    fwrite($fp, $headers."\r\n".$body."\r\n.\r\n");
    $r = smtp_read($fp);
    smtp_cmd($fp, "QUIT");
    fclose($fp);
    if (strpos($r, '250') !== false) return ['ok'=>true];
    return ['ok'=>false,'error'=>'Send failed: '.$r];
}

function smtp_cmd($fp, $cmd) {
    fwrite($fp, $cmd."\r\n");
    return smtp_read($fp);
}

function smtp_read($fp) {
    $data = '';
    while ($line = fgets($fp, 515)) {
        $data .= $line;
        if (substr($line, 3, 1) === ' ') break;
    }
    return $data;
}

$result = smtp_send($smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_user, $from_name, $email, $subject, $message);
echo json_encode($result);
