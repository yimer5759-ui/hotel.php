<?php
/**
 * Application Configuration
 * Hotel Booking Management System
 */

// ── Environment ──────────────────────────────────────────────
define('APP_ENV',  'development');   // 'development' | 'production'
define('APP_NAME', 'Grand Azure Hotel');
define('APP_URL',  'http://localhost/hotel,php');
define('APP_VERSION', '1.0.0');

// ── Database ─────────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'hotel_booking');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ── Security ─────────────────────────────────────────────────
define('SECRET_KEY',    'CHANGE_THIS_TO_RANDOM_64_CHAR_STRING_IN_PRODUCTION');
define('CSRF_TOKEN_NAME', '_csrf_token');
define('SESSION_LIFETIME', 7200);   // 2 hours

// ── Paths ─────────────────────────────────────────────────────
define('BASE_PATH',    dirname(__DIR__));
define('VIEWS_PATH',   BASE_PATH . '/views');
define('UPLOADS_PATH', BASE_PATH . '/assets/uploads');
define('UPLOADS_URL',  APP_URL    . '/assets/uploads');

// ── Upload limits ─────────────────────────────────────────────
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);  // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg','image/png','image/webp','image/gif']);

// ── Pagination ────────────────────────────────────────────────
define('PER_PAGE', 15);

// ── Email (PHPMailer optional) ────────────────────────────────
define('MAIL_ENABLED', false);       // Set true when SMTP is configured

// ── Timezone ─────────────────────────────────────────────────
date_default_timezone_set('America/New_York');

// ── Error Reporting ───────────────────────────────────────────
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ── Session Configuration ─────────────────────────────────────
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
