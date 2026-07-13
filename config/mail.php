<?php

declare(strict_types=1);

/**
 * Mail / SMTP configuration.
 *
 * Values are read from environment variables first, then fall back to the
 * defaults below. Set them in your .env file or web-server vhost config.
 *
 * Supported encryption values: 'tls' (STARTTLS on 587), 'ssl' (SMTPS on 465), '' (plain 25)
 */
return [

    /*
    |-------------------------------------------------------------------
    | Default mailer driver
    |-------------------------------------------------------------------
    | 'smtp'  — sends via SMTP (production)
    | 'log'   — writes to a log file instead of sending (development)
    | 'null'  — discards every email silently (testing)
    */
    'driver' => env('MAIL_DRIVER', 'smtp'),

    /*
    |-------------------------------------------------------------------
    | SMTP connection
    |-------------------------------------------------------------------
    */
    'host'       => env('MAIL_HOST',       'smtp.sendgrid.net'),
    'port'       => (int) env('MAIL_PORT', 587),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),   // 'tls' | 'ssl' | ''
    'username'   => env('MAIL_USERNAME',   ''),
    'password'   => env('MAIL_PASSWORD',   ''),
    'timeout'    => (int) env('MAIL_TIMEOUT', 30),   // socket timeout in seconds

    /*
    |-------------------------------------------------------------------
    | Global "From" address
    |-------------------------------------------------------------------
    */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@pharmawebcast.com'),
        'name'    => env('MAIL_FROM_NAME',    'PharmaWebcast'),
    ],

    /*
    |-------------------------------------------------------------------
    | Global "Reply-To" (optional)
    |-------------------------------------------------------------------
    */
    'reply_to' => [
        'address' => env('MAIL_REPLY_TO_ADDRESS', 'support@pharmawebcast.com'),
        'name'    => env('MAIL_REPLY_TO_NAME',    'PharmaWebcast Support'),
    ],

    /*
    |-------------------------------------------------------------------
    | Queue settings
    |-------------------------------------------------------------------
    | max_attempts : how many times the worker retries a failed send
    | retry_delay  : seconds before a failed job becomes eligible again
    | batch_size   : how many emails the worker picks up per run
    */
    'queue' => [
        'max_attempts' => (int) env('MAIL_MAX_ATTEMPTS', 3),
        'retry_delay'  => (int) env('MAIL_RETRY_DELAY',  300),  // 5 min
        'batch_size'   => (int) env('MAIL_BATCH_SIZE',   20),
    ],

    /*
    |-------------------------------------------------------------------
    | Log driver path (used when driver = 'log')
    |-------------------------------------------------------------------
    */
    'log_path' => env('MAIL_LOG_PATH', BASE_PATH . '/storage/logs/mail.log'),

];
