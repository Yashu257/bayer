<?php
/**
 * Public Entry Point
 * All HTTP requests are routed through this file via .htaccess
 */
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH',  BASE_PATH . '/app');
define('CORE_PATH', BASE_PATH . '/core');

require_once CORE_PATH . '/bootstrap.php';
