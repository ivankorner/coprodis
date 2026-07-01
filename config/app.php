<?php

define('APP_NAME', $_ENV['APP_NAME'] ?? 'COPRODIS');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/coprodis');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));

define('TIMEZONE', $_ENV['TIMEZONE'] ?? 'America/Argentina/Buenos_Aires');
define('PAGINATION_LIMIT', (int)($_ENV['PAGINATION_LIMIT'] ?? 25));

define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? '');
define('SMTP_PORT', (int)($_ENV['SMTP_PORT'] ?? 587));
define('SMTP_ENCRYPTION', $_ENV['SMTP_ENCRYPTION'] ?? 'tls');
define('SMTP_USER', $_ENV['SMTP_USER'] ?? '');
define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? '');
define('SMTP_FROM_EMAIL', $_ENV['SMTP_FROM_EMAIL'] ?? '');
define('SMTP_FROM_NAME', $_ENV['SMTP_FROM_NAME'] ?? 'CO.PRO.DIS');

date_default_timezone_set(TIMEZONE);

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
