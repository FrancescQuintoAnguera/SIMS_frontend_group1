<?php
/**
 * Update Profile Handler
 * Processes profile updates with proper validation
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

// Get and sanitize input data
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$birth_date = trim($_POST['birth_date'] ?? '');
$iban = trim($_POST['iban'] ?? '');
$nationality_id = intval($_POST['nationality_id'] ?? 0);

// Validation array
$errors = [];

// Validate fullname
if (empty($fullname)) {
    $errors[] = 'Full name is required';
} elseif (strlen($fullname) > 50) {
    $errors[] = 'Full name must be less than 50 characters';
} elseif (!preg_match("/^[a-zA-ZàáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçčšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆČŠŽ∂ð ,.'-]+$/u", $fullname)) {
    $errors[] = 'Full name contains invalid characters';
}

// Validate email
if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
} elseif (strlen($email) > 100) {
    $errors[] = 'Email must be less than 100 characters';
}

// Validate phone (optional)
if (!empty($phone)) {
    // Remove spaces and dashes for validation
    $phone_clean = preg_replace('/[\s\-\(\)]/', '', $phone);
    if (!preg_match('/^\+?[0-9]{9,15}$/', $phone_clean)) {
        $errors[] = 'Invalid phone number format';
    }
    if (strlen($phone) > 20) {
        $errors[] = 'Phone number must be less than 20 characters';
    }
}

// Validate birth date (optional)
if (!empty($birth_date)) {
    $date_obj = DateTime::createFromFormat('Y-m-d', $birth_date);
    if (!$date_obj || $date_obj->format('Y-m-d') !== $birth_date) {
        $errors[] = 'Invalid birth date format';
    } else {
        // Check if user is at least 18 years old
        $today = new DateTime();
        $age = $today->diff($date_obj)->y;
        if ($age < 18) {
            $errors[] = 'You must be at least 18 years old';
        }
        if ($age > 120) {
            $errors[] = 'Invalid birth date';
        }
    }
}

// Validate IBAN (optional)
if (!empty($iban)) {
    // Remove spaces for validation
    $iban_clean = str_replace(' ', '', $iban);
    if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/', $iban_clean)) {
        $errors[] = 'Invalid IBAN format';
    }
    if (strlen($iban) > 34) {
        $errors[] = 'IBAN must be less than 34 characters';
    }
}

// Validate nationality_id (optional)
if ($nationality_id > 0) {
    $db = Database::getMariaDBConnection();
    $stmt = $db->prepare("SELECT id FROM nationalities WHERE id = ?");
    $stmt->execute([$nationality_id]);
    if ($result->rowCount() === 0) {
        $errors[] = 'Invalid nationality selected';
    }
    $stmt->close();
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

// Check if email is already taken by another user
$db = Database::getMariaDBConnection();
$stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->execute([$email, $user_id]);

if ($result->rowCount() > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Email is already in use by another account'
    ]);
    $stmt->close();
    exit;
}
$stmt->close();

// Update user profile
$update_query = "UPDATE users SET 
    fullname = ?,
    email = ?,
    phone = ?,
    birth_date = ?,
    iban = ?,
    nationality_id = ?
    WHERE id = ?";

$stmt = $db->prepare($update_query);

// Handle NULL values for optional fields
$birth_date_value = !empty($birth_date) ? $birth_date : null;
$phone_value = !empty($phone) ? $phone : null;
$iban_value = !empty($iban) ? $iban : null;
$nationality_value = $nationality_id > 0 ? $nationality_id : null;

if ($stmt->execute([$fullname, $email, $phone_value, $birth_date_value, $iban_value, $nationality_value, $user_id])) {
    // Get updated user data
    $stmt->close();
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.email,
            u.username,
            u.fullname,
            u.phone,
            u.birth_date,
            u.iban,
            n.name as nationality
        FROM users u
        LEFT JOIN nationalities n ON u.nationality_id = n.id
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $updated_user = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'user' => $updated_user
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update profile. Please try again.'
    ]);
}

$stmt->close();
?>
