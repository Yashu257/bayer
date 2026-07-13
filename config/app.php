<?php

declare(strict_types=1);

return [
    'name'     => $_ENV['APP_NAME']  ?? 'PharmaWebcast',
    'env'      => $_ENV['APP_ENV']   ?? 'production',
    'debug'    => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url'      => $_ENV['APP_URL']   ?? 'http://localhost',
    'timezone' => 'UTC',
    'charset'  => 'UTF-8',

    'secret_key' => $_ENV['APP_SECRET'] ?? 'change-this-in-production-32chars!!',
];
