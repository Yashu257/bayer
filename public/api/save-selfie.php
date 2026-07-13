<?php

declare(strict_types=1);

/**
 * save-selfie.php — Standalone selfie upload endpoint for the static preview.
 *
 * The static preview (preview.html, served by `php -S -t public`) does NOT route
 * through the app's front controller, so the proper Api\SelfieController route
 * (/api/v1/selfies/upload) is unreachable here. This mirrors that controller's
 * logic directly so the preview can save selfies to public/uploads/selfies/ and
 * record the filename in the `selfies` DB table.
 *
 * Accepts: POST JSON { "image": "data:image/png;base64,...." }
 * Returns: JSON { success, image_path, image_name } or { success:false, error }
 */

header('Content-Type: application/json; charset=utf-8');

// --- Only POST -------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Use POST.']);
    exit;
}

// --- Read JSON body --------------------------------------------------------
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!is_array($body) || empty($body['image'])) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Image is required.']);
    exit;
}

// --- Decode & validate base64 data URI -------------------------------------
$imageData = $body['image'];

if (!preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $imageData, $type)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid image format.']);
    exit;
}

$extension = ($type[1] === 'jpeg') ? 'jpg' : $type[1];
$binary    = base64_decode(preg_replace('/^data:image\/(png|jpeg|jpg);base64,/', '', $imageData), true);

if ($binary === false || strlen($binary) < 100) {
    // Guard against empty/garbage payloads (the earlier 70-byte test file).
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Image data is empty or corrupt.']);
    exit;
}

// --- Save file to public/uploads/selfies/ ----------------------------------
$fileName   = 'selfie_' . uniqid('', true) . '.' . $extension;
$uploadDir  = __DIR__ . '/../uploads/selfies/';
$filePath   = $uploadDir . $fileName;
$publicPath = '/uploads/selfies/' . $fileName;

if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Upload directory could not be created.']);
    exit;
}

if (file_put_contents($filePath, $binary) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save image file.']);
    exit;
}

// --- Record filename in the `selfies` table --------------------------------
// Load DB credentials from .env if present, else fall back to config defaults.
$env = [];
$envFile = __DIR__ . '/../../.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
    }
}

$dbHost = $env['DB_HOST']     ?? '127.0.0.1';
$dbPort = $env['DB_PORT']     ?? '3306';
$dbName = $env['DB_DATABASE'] ?? 'pharma_webcast';
$dbUser = $env['DB_USERNAME'] ?? 'root';
$dbPass = $env['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // event_id defaults to 1 (single-event preview) — mirrors Api\SelfieController.
    $stmt = $pdo->prepare(
        'INSERT INTO selfies (event_id, image_path, image_name, created_at, updated_at)
         VALUES (:event_id, :image_path, :image_name, NOW(), NOW())'
    );
    $stmt->execute([
        ':event_id'   => 1,
        ':image_path' => $publicPath,
        ':image_name' => $fileName,
    ]);

    echo json_encode([
        'success'    => true,
        'image_path' => $publicPath,
        'image_name' => $fileName,
        'id'         => (int) $pdo->lastInsertId(),
    ]);
} catch (Throwable $e) {
    // File saved but DB insert failed — report so it isn't silently lost.
    http_response_code(500);
    echo json_encode([
        'success'    => false,
        'error'      => 'File saved but database record failed: ' . $e->getMessage(),
        'image_path' => $publicPath,
        'image_name' => $fileName,
    ]);
}
