<?php
/**
 * Session Management Utilities
 * Provides functions for session handling and authentication checks
 */

/**
 * Start session securely
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
        ini_set('session.cookie_samesite', 'Lax');
        
        session_start();
        
        // Regenerate session ID periodically to prevent session fixation
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) {
            // Regenerate session ID every 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    startSecureSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    startSecureSession();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 * @return string|null Username or null if not logged in
 */
function getCurrentUsername() {
    startSecureSession();
    return $_SESSION['username'] ?? null;
}

/**
 * Check if user is admin
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    startSecureSession();
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Require login - redirect to login page if not logged in
 * @param string $redirect_url URL to redirect to after login
 */
function requireLogin($redirect_url = '/pages/auth/login.html') {
    if (!isLoggedIn()) {
        header("Location: $redirect_url");
        exit;
    }
}

/**
 * Require admin - redirect if not admin
 * @param string $redirect_url URL to redirect to if not admin
 */
function requireAdmin($redirect_url = '/index.php') {
    requireLogin();
    if (!isAdmin()) {
        header("Location: $redirect_url");
        exit;
    }
}

/**
 * Set user session data
 * @param array $user_data User data array
 */
function setUserSession($user_data) {
    startSecureSession();
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['is_admin'] = $user_data['is_admin'] ?? 0;
}

/**
 * Clear user session
 */
function clearUserSession() {
    startSecureSession();
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['is_admin']);
}

/**
 * Get CSRF token
 * @return string CSRF token
 */
function getCsrfToken() {
    startSecureSession();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCsrfToken($token) {
    startSecureSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>