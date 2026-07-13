<?php

declare(strict_types=1);

/**
 * save-question.php — Standalone endpoint that records an attendee Q&A question
 * into the `questions` table (the static preview otherwise only uses localStorage).
 *
 * Accepts: POST JSON { "name": "...", "email": "...", "question": "..." }
 * Returns: JSON { success, id } or { success:false, error }
 *
 * Uses its own PDO connection. Mirrors the save-selfie.php / save-user.php pattern.
 */

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Use POST.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

$email    = strtolower(trim((string) ($body['email']    ?? '')));
$question = trim((string) ($body['question'] ?? ''));

if ($question === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Question text is required.']);
    exit;
}

// --- DB connection (reads .env) --------------------------------------------
$env = [];
$envFile = __DIR__ . '/../../.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
    }
}
$dbHost = $env['DB_HOST'] ?? '127.0.0.1';
$dbPort = $env['DB_PORT'] ?? '3306';
$dbName = $env['DB_DATABASE'] ?? 'pharma_webcast';
$dbUser = $env['DB_USERNAME'] ?? 'root';
$dbPass = $env['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
        $dbUser, $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Link the question to a user row if this email is already registered (optional).
    $userId = null;
    if ($email !== '') {
        $q = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $q->execute([':email' => $email]);
        $found = $q->fetchColumn();
        if ($found !== false) {
            $userId = (int) $found;
        }
    }

    // webcast_id = 1 (the seeded webcast for the placeholder event).
    $stmt = $pdo->prepare(
        "INSERT INTO questions (webcast_id, user_id, body, moderation_status, status, created_at, updated_at)
         VALUES (1, :user_id, :body, 'pending', 'active', NOW(), NOW())"
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':body'    => $question,
    ]);

    echo json_encode(['success' => true, 'id' => (int) $pdo->lastInsertId()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
