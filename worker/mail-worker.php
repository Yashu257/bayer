#!/usr/bin/env php
<?php

/**
 * Mail Queue Worker — CLI entry point.
 *
 * Usage:
 *   php worker/mail-worker.php
 *   php worker/mail-worker.php --daemon          # run every 60s forever
 *   php worker/mail-worker.php --once            # process one batch then exit (default)
 *
 * Cron (one batch per minute):
 *   * * * * * php /var/www/pharma-webcast/worker/mail-worker.php >> /var/log/pharmawebcast/mail-worker.log 2>&1
 *
 * Supervisor (daemon mode — auto-restart on crash):
 *   command=php /var/www/pharma-webcast/worker/mail-worker.php --daemon
 */

// ── Bootstrap ────────────────────────────────────────────────────────────────

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/core/helpers.php';     // env(), dd() etc.
require_once BASE_PATH . '/core/autoload.php';    // PSR-4 class loader
require_once BASE_PATH . '/config/database.php';  // DB constants / connection

// ── Arguments ────────────────────────────────────────────────────────────────

$opts   = getopt('', ['daemon', 'once', 'sleep::']);
$daemon = isset($opts['daemon']);
$sleep  = (int) ($opts['sleep'] ?? 60);   // seconds between daemon iterations

// ── Run ──────────────────────────────────────────────────────────────────────

$worker = new \Core\Mail\MailWorker();

if ($daemon) {
    echo '[' . date('Y-m-d H:i:s') . '] Mail worker started in daemon mode (sleep=' . $sleep . 's).' . PHP_EOL;
    while (true) {
        $worker->run();
        sleep($sleep);
    }
} else {
    $worker->run();
}
