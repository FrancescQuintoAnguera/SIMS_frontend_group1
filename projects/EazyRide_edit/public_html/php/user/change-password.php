<?php
/**
 * Change Password Handler
 * Secure password change functionality with current password verification and new password hashing
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth/session.php';

// Start session and check authentication
startSecureSession();
requireLogin();

// Set JSON response header
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Verify CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrf_token)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid security token'
    ]);
    exit;
}

$user_id = getCurrentUserId();

// Get input data
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation array
$errors = [];

// Validate current password
if (empty($current_password)) {
    $errors[] = 'Current password is required';
}

// Validate new password
if (empty($new_password)) {
    $errors[] = 'New password is required';
} else {
    // Password strength requirements
    if (strlen($new_password) < 8) {
        $errors[] = 'New password must be at least 8 characters long';
    }
    if (strlen($new_password) > 255) {
        $errors[] = 'New password must be less than 255 characters';
    }
    if (!preg_match('/[A-Z]/', $new_password)) {
        $errors[] = 'New password must contain at least one uppercase letter';
    }
    if (!preg_match('/[a-z]/', $new_password)) {
        $errors[] = 'New password must contain at least one lowercase letter';
    }
    if (!preg_match('/[0-9]/', $new_password)) {
        $errors[] = 'New password must contain at least one number';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
        $errors[] = 'New password must contain at least one special character';
    }
}

// Validate confirm password
if (empty($confirm_password)) {
    $errors[] = 'Password confirmation is required';
} elseif ($new_password !== $confirm_password) {
    $errors[] = 'New password and confirmation do not match';
}

// Check if new password is same as current
if (!empty($current_password) && !empty($new_password) && $current_password === $new_password) {
    $errors[] = 'New password must be different from current password';
}

// If there are validation errors, return them
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $errors
    ]);
    exit;
}

// Get current password hash from database
$db = Database::getMariaDBConnection();
$stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$user_id]);

if ($result->rowCount() === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'User not found'
    ]);
    $stmt->close();
    exit;
}

$user = $result->fetch(PDO::FETCH_ASSOC);
$stmt->close();

// Verify current password
if (!password_verify($current_password, $user['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Current password is incorrect'
    ]);
    exit;
}

// Hash new password
$new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update password in database
$stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
// bind_param removed - use array params in execute()

if ($stmt->execute()) {
    // Log password change (optional - for security audit)
    error_log("Password changed for user ID: {$user_id} at " . date('Y-m-d H:i:s'));
    
    echo json_encode([
        'success' => true,
        'message' => 'Password changed successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to change password. Please try again.'
    ]);
}

$stmt->close();
?>
