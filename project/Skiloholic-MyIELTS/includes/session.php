<?php
/**
 * Session Management
 * MyIELTS - Secure session handling and authentication
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Get authenticated user data
 *
 * @return array|null Returns user array or null if not logged in
 */
function get_authenticated_user() {
    if (!is_logged_in()) {
        return null;
    }

    // Fetch fresh data from database
    // Using email_verified (not is_verified) and password_hash based on actual DB structure
    $user = db_fetch(
        "SELECT id, full_name, email, role, email_verified, avatar_url, target_band, exam_date,
                password_hash, created_at, updated_at
         FROM users WHERE id = ?",
        [$_SESSION['user_id']]
    );

    return $user;
}

/**
 * Login user and create session
 *
 * @param array $user User data
 * @param bool $remember Whether to set remember me cookie
 */
function login_user($user, $remember = false) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['email_verified'] = $user['email_verified'];
    $_SESSION['logged_in_at'] = time();

    // Regenerate session ID for security
    session_regenerate_id(true);

    // Set remember me cookie if requested
    if ($remember) {
        $token = bin2hex(random_bytes(32));

        // Store token in database
        db_execute(
            "UPDATE users SET remember_token = ? WHERE id = ?",
            [$token, $user['id']]
        );

        // Set cookie (30 days)
        setcookie(
            'remember_me',
            $token,
            time() + REMEMBER_ME_DURATION,
            '/',
            '',
            false, // Set to true for HTTPS only
            true   // HTTP only
        );
    }
}

/**
 * Logout user and destroy session
 */
function logout_user() {
    // Clear remember me cookie and token
    if (isset($_COOKIE['remember_me'])) {
        if (isset($_SESSION['user_id'])) {
            db_execute(
                "UPDATE users SET remember_token = NULL WHERE id = ?",
                [$_SESSION['user_id']]
            );
        }

        setcookie('remember_me', '', time() - 3600, '/');
        unset($_COOKIE['remember_me']);
    }

    // Destroy session
    $_SESSION = [];
    session_destroy();
}

/**
 * Check if user is remembered and auto-login
 */
function check_remember_me() {
    if (isset($_COOKIE['remember_me']) && !is_logged_in()) {
        $token = $_COOKIE['remember_me'];

        $user = db_fetch(
            "SELECT * FROM users WHERE remember_token = ? AND email_verified = 1",
            [$token]
        );

        if ($user) {
            login_user($user, false);
            return true;
        } else {
            // Invalid token, clear cookie
            setcookie('remember_me', '', time() - 3600, '/');
            unset($_COOKIE['remember_me']);
        }
    }

    return false;
}

/**
 * Require email verification
 */
function require_email_verification() {
    if (!isset($_SESSION['email_verified']) || !$_SESSION['email_verified']) {
        redirect(BASE_URL . 'auth/verify-email.php');
    }
}

/**
 * Check if current page requires authentication
 */
function check_auth() {
    check_remember_me();

    $public_pages = [
        'login.php',
        'register.php',
        'forgot-password.php',
        'reset-password.php',
        'verify-email.php'
    ];

    $current_page = basename($_SERVER['PHP_SELF']);

    // If not on public page and not logged in, redirect to login
    if (!in_array($current_page, $public_pages) && !is_logged_in()) {
        redirect(BASE_URL . 'auth/login.php');
    }

    // If logged in but email not verified (except on verification page)
    if (is_logged_in() && $current_page !== 'verify-email.php') {
        require_email_verification();
    }
}
