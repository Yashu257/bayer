<?php

declare(strict_types=1);

/**
 * save-user.php — Standalone endpoint that records a preview attendee login
 * into the `users` table (the static preview otherwise only uses localStorage).
 *
 * Accepts: POST JSON { "name": "...", "email": "..." }
 * Returns: JSON { success, id } or { success:false, error }
 *
 * Uses its own PDO connection (independent of config/database.php) so it works
 * from the static preview server. Mirrors the save-selfie.php pattern.
 */

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Use POST.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

$name  = trim((string) ($body['name']  ?? ''));
$email = strtolower(trim((string) ($body['email'] ?? '')));

if ($name === '' || $email === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Name and email are required.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid email address.']);
    exit;
}

// Split "full name" into first / last (users table requires both, NOT NULL).
$parts     = preg_split('/\s+/', $name, 2);
$firstName = $parts[0];
$lastName  = $parts[1] ?? '';

// --- DB connection (reads .env, same as save-selfie.php) -------------------
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

    // role_id = 2 (Attendee). email is UNIQUE, so upsert: on repeat login just
    // refresh the name + last_login_at rather than erroring on the duplicate.
    $stmt = $pdo->prepare(
        "INSERT INTO users (role_id, first_name, last_name, email, status, last_login_at, created_at, updated_at)
         VALUES (2, :first, :last, :email, 'active', NOW(), NOW(), NOW())
         ON DUPLICATE KEY UPDATE
            first_name    = VALUES(first_name),
            last_name     = VALUES(last_name),
            status        = 'active',
            last_login_at = NOW(),
            updated_at    = NOW()"
    );
    $stmt->execute([
        ':first' => $firstName,
        ':last'  => $lastName,
        ':email' => $email,
    ]);

    // Get the id (lastInsertId is 0 on an update, so look it up).
    $id = (int) $pdo->lastInsertId();
    if ($id === 0) {
        $q = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $q->execute([':email' => $email]);
        $id = (int) $q->fetchColumn();
    }

    echo json_encode(['success' => true, 'id' => $id]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
