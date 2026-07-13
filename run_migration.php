<?php

define('BASE_PATH', __DIR__);
define('APP_PATH',  BASE_PATH . '/app');
define('CORE_PATH', BASE_PATH . '/core');

require_once BASE_PATH . '/core/helpers.php';

// Load .env manually
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

// Autoload Core classes
spl_autoload_register(function (string $class): void {
    $namespaceMap = [
        'Core\\' => CORE_PATH . '/',
    ];

    foreach ($namespaceMap as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }
        $relative = substr($class, strlen($prefix));
        $file     = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Initialize Database
\Core\Database\Database::init(require BASE_PATH . '/config/database.php');

try {
    // Create selfies table
    $sql = "CREATE TABLE IF NOT EXISTS selfies (
        id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id             BIGINT UNSIGNED     NULL,
        registration_id     BIGINT UNSIGNED     NULL,
        event_id            BIGINT UNSIGNED NOT NULL,
        image_path          VARCHAR(500)        NOT NULL,
        image_name          VARCHAR(255)        NOT NULL,
        created_at          DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at          DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX idx_selfies_user_id (user_id),
        INDEX idx_selfies_registration_id (registration_id),
        INDEX idx_selfies_event_id (event_id),
        CONSTRAINT fk_selfies_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
        CONSTRAINT fk_selfies_registration FOREIGN KEY (registration_id) REFERENCES registrations (id) ON DELETE SET NULL,
        CONSTRAINT fk_selfies_event FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    \Core\Database\Database::execute($sql);
    echo "✅ Selfies table created successfully!\n";

    // Check if event with id=1 exists, if not try to insert a dummy one (skip if tables don't exist yet)
    try {
        $event = \Core\Database\Database::queryOne("SELECT * FROM events WHERE id = ? LIMIT 1", [1]);
        if (!$event) {
            // Check if roles table exists
            $roleExists = \Core\Database\Database::queryOne("SHOW TABLES LIKE 'roles'");
            if ($roleExists) {
                $role = \Core\Database\Database::queryOne("SELECT * FROM roles WHERE slug = 'super_admin' LIMIT 1");
                if (!$role) {
                    \Core\Database\Database::execute("INSERT INTO roles (name, slug, description) VALUES ('Super Admin', 'super_admin', 'Full access')");
                    $roleId = \Core\Database\Database::lastInsertId();
                } else {
                    $roleId = $role['id'];
                }

                // Check if admins table exists
                $adminExists = \Core\Database\Database::queryOne("SHOW TABLES LIKE 'admins'");
                if ($adminExists) {
                    $admin = \Core\Database\Database::queryOne("SELECT * FROM admins WHERE id = 1 LIMIT 1");
                    if (!$admin) {
                        \Core\Database\Database::execute("INSERT INTO admins (role_id, first_name, last_name, email, password_hash) VALUES (?, 'Admin', 'User', 'admin@example.com', ?)", 
                            [$roleId, password_hash('admin123', PASSWORD_DEFAULT)]);
                        $adminId = \Core\Database\Database::lastInsertId();
                    } else {
                        $adminId = $admin['id'];
                    }

                    // Insert dummy event
                    \Core\Database\Database::execute("INSERT INTO events (brand_id, created_by, title, slug, description, event_type, timezone, starts_at, ends_at, status) VALUES (NULL, ?, 'Demo Event', 'demo-event', 'Demo event for preview', 'live', 'UTC', NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY), 'published')", [$adminId]);
                    echo "✅ Dummy event created with id = 1!\n";
                }
            }
        } else {
            echo "ℹ️ Event with id=1 already exists!\n";
        }
    } catch (Exception $e) {
        echo "ℹ️ Skipping dummy event creation (tables may not exist yet): " . $e->getMessage() . "\n";
    }

    echo "\n🎉 Migration complete!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📁 File: " . $e->getFile() . "\n";
    echo "📝 Line: " . $e->getLine() . "\n";
}
