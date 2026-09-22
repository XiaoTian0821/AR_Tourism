<?php
/**
 * AR Tourism Explorer - Configuration
 * Central configuration file for the application
 */

// Application Settings
define('APP_NAME', 'AR Tourism Explorer');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/AR_Tourism');
define('ADMIN_URL', APP_URL . '/admin');

// Database Settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'ar_tourism');
define('DB_USER', 'root');
define('DB_PASS', '123456');
define('DB_CHARSET', 'utf8mb4');

// Security Settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_THROTTLE_TIME', 900); // 15 minutes

// Upload Settings
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('AR_TARGETS_PATH', __DIR__ . '/../assets/ar-targets/');
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Pagination Settings
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// AR Settings
define('MINDAR_TARGET_DIR', 'assets/ar-targets/');
define('DEFAULT_AR_VIEWPORT_WIDTH', 1280);
define('DEFAULT_AR_VIEWPORT_HEIGHT', 720);

// Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not exists
if (empty($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}

// Auto-load composer if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
?>
