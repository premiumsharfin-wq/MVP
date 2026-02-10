<?php
/**
 * Global Configuration
 * MyIELTS Platform
 */

// Site Information
define('SITE_NAME', 'MyIELTS');
define('SITE_TAGLINE', 'Powered by Skiloholic');
define('DEVELOPER', 'Sharfin Hossain');
define('ORG_TYPE', 'Non-Profit Organization');

// Base URL - Production
if (isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || $_SERVER['HTTP_HOST'] === '127.0.0.1')) {
    define('BASE_URL', 'http://localhost/Skiloholic-MyIELTS/');
} else {
    define('BASE_URL', 'https://skiloholic.com/');
}
define('ADMIN_URL', BASE_URL . 'admin/');

// Paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('LOGO_PATH', ROOT_PATH . 'Logo/');

// Upload directories
define('TEST_IMAGES_DIR', UPLOADS_PATH . 'test-images/');
define('SUBMISSION_IMAGES_DIR', UPLOADS_PATH . 'submission-images/');

// URLs for uploads
define('UPLOADS_URL', BASE_URL . 'uploads/');
define('TEST_IMAGES_URL', UPLOADS_URL . 'test-images/');
define('SUBMISSION_IMAGES_URL', UPLOADS_URL . 'submission-images/');
define('LOGO_URL', BASE_URL . 'Logo/');

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // HTTPS enabled for production

// Error reporting - production mode
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disabled for production security
ini_set('log_errors', 1);
ini_set('error_log', ROOT_PATH . 'error.log');

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);

// Email settings
define('VERIFICATION_CODE_LENGTH', 6);
define('VERIFICATION_CODE_EXPIRY', 15); // minutes

// Remember me cookie duration (30 days)
define('REMEMBER_ME_DURATION', 30 * 24 * 60 * 60);

// Coming soon date
define('OTHER_TESTS_AVAILABLE_DATE', '15 July 2026');

// Pagination
define('ITEMS_PER_PAGE', 10);

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_TIME_NAME', 'csrf_token_time');
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour

// Auto-refresh intervals (milliseconds)
define('STATUS_PAGE_REFRESH_INTERVAL', 30000); // 30 seconds

/**
 * Generate CSRF token
 */
if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
            $_SESSION[CSRF_TOKEN_TIME_NAME] = time();
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
}

/**
 * Verify CSRF token
 */
if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token) {
        if (!isset($_SESSION[CSRF_TOKEN_NAME]) || !isset($_SESSION[CSRF_TOKEN_TIME_NAME])) {
            return false;
        }

        // Check if token expired
        if (time() - $_SESSION[CSRF_TOKEN_TIME_NAME] > CSRF_TOKEN_EXPIRY) {
            unset($_SESSION[CSRF_TOKEN_NAME]);
            unset($_SESSION[CSRF_TOKEN_TIME_NAME]);
            return false;
        }

        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
}

/**
 * Sanitize input
 */
if (!function_exists('sanitize')) {
    function sanitize($input) {
        if (is_array($input)) {
            return array_map('sanitize', $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Generate random verification code
 */
if (!function_exists('generate_verification_code')) {
    function generate_verification_code() {
        return str_pad(random_int(0, 999999), VERIFICATION_CODE_LENGTH, '0', STR_PAD_LEFT);
    }
}

/**
 * Format datetime for display
 */
if (!function_exists('format_datetime')) {
    function format_datetime($datetime) {
        if (!$datetime) return 'N/A';
        $dt = new DateTime($datetime);
        return $dt->format('d M Y, h:i A');
    }
}

/**
 * Time ago function
 */
if (!function_exists('time_ago')) {
    function time_ago($datetime) {
        $timestamp = strtotime($datetime);
        $difference = time() - $timestamp;

        $periods = [
            'year' => 31536000,
            'month' => 2592000,
            'week' => 604800,
            'day' => 86400,
            'hour' => 3600,
            'minute' => 60,
            'second' => 1
        ];

        foreach ($periods as $key => $value) {
            if ($difference >= $value) {
                $time = floor($difference / $value);
                return $time . ' ' . $key . ($time > 1 ? 's' : '') . ' ago';
            }
        }

        return 'Just now';
    }
}

/**
 * Redirect helper
 */
if (!function_exists('redirect')) {
    function redirect($url, $permanent = false) {
        $status = $permanent ? 301 : 302;
        header("Location: $url", true, $status);
        exit();
    }
}

/**
 * Check if user is logged in
 */
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']);
    }
}

/**
 * Check if user is admin
 */
if (!function_exists('is_admin')) {
    function is_admin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

/**
 * Check if user is examiner or admin
 */
if (!function_exists('is_examiner')) {
    function is_examiner() {
        return isset($_SESSION['role']) && in_array($_SESSION['role'], ['examiner', 'admin']);
    }
}

/**
 * Require login
 */
if (!function_exists('require_login')) {
    function require_login() {
        if (!is_logged_in()) {
            redirect(BASE_URL . 'auth/login.php');
        }
    }
}

/**
 * Require admin
 */
if (!function_exists('require_admin')) {
    function require_admin() {
        require_login();
        if (!is_admin()) {
            redirect(BASE_URL . 'index.php');
        }
    }
}
