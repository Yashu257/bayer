<?php

declare(strict_types=1);

return [
    'name'            => 'PHARMA_SID',
    'lifetime'        => (int) ($_ENV['SESSION_LIFETIME'] ?? 120) * 60,   // convert to seconds
    'path'            => '/',
    'domain'          => $_ENV['SESSION_DOMAIN'] ?? '',
    'secure'          => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'http_only'       => true,
    'same_site'       => 'Lax',   // 'Strict' | 'Lax' | 'None'
    'save_path'       => BASE_PATH . '/storage/sessions',
    'gc_maxlifetime'  => 7200,
];
