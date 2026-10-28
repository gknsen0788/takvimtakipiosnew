<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Istanbul');

// Autoloading helper for src directory
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', '31536000');
    ini_set('session.cookie_lifetime', '31536000');
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

define('APP_NAME', 'TakvimTakip');
define('CRON_SECRET', 'takvim_cron_sec_8f9a2b4c');

// OneSignal Push Bildirim Ayarları (onesignal.com panelinden alınır)
if (!defined('ONESIGNAL_APP_ID')) {
    define('ONESIGNAL_APP_ID', getenv('ONESIGNAL_APP_ID') ?: 'ONESIGNAL_APP_ID_BURAYA');
}
if (!defined('ONESIGNAL_REST_API_KEY')) {
    define('ONESIGNAL_REST_API_KEY', getenv('ONESIGNAL_REST_API_KEY') ?: 'ONESIGNAL_REST_API_KEY_BURAYA');
}

if (!defined('APP_URL')) {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = str_replace('\\', '/', dirname($scriptName));
    if (str_ends_with($dir, '/public')) {
        $dir = substr($dir, 0, -7);
    }
    
    $reqUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    if ($dir !== '' && $dir !== '/' && !str_starts_with($reqUri, $dir)) {
        $basePath = '';
    } else {
        $basePath = rtrim($dir, '/');
    }
    
    define('APP_URL', $scheme . '://' . $host . $basePath);
}

define('DB_PATH', __DIR__ . '/../database/database.sqlite');
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('UPLOAD_URL', APP_URL . '/public/uploads/');

// -------------------------------------------------------------
// SMTP E-POSTA SUNUCU AYARLARI
// -------------------------------------------------------------
define('SMTP_HOST', 'mail.kurumsaleposta.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'destek@takvimtakip.com.tr');
define('SMTP_PASS', '623109023gkMK');
define('SMTP_FROM_NAME', 'TakvimTakip');


