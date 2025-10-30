<?php
/**
 * Profile Display Handler
 * Retrieves and displays user profile information
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth/session.php';

// Start session and check authentication
startSecureSession();
requireLogin();

$user_id = getCurrentUserId();

// Get database connection
$db = Database::getMariaDBConnection();

// Fetch user profile data
$stmt = $db->prepare("
    SELECT 
        u.id,
        u.email,
        u.username,
        u.fullname,
        u.phone,
        u.birth_date,
        u.is_admin,
        u.iban,
        u.driver_license_photo,
        u.minute_balance,
        u.created_at,
        n.name as nationality
    FROM users u
    LEFT JOIN nationalities n ON u.nationality_id = n.id
    WHERE u.id = ?
");

$stmt->execute([$user_id]);

if ($result->rowCount() === 0) {
    // User not found
    header("Location: /php/auth/logout.php");
    exit;
}

$user = $result->fetch(PDO::FETCH_ASSOC);
$stmt->close();

// Check if user has an avatar
$avatar_path = "/images/avatars/user_{$user_id}.jpg";
$avatar_exists = file_exists(__DIR__ . '/../../images/avatars/user_' . $user_id . '.jpg');

if (!$avatar_exists) {
    // Check for PNG format
    $avatar_path_png = "/images/avatars/user_{$user_id}.png";
    $avatar_exists_png = file_exists(__DIR__ . '/../../images/avatars/user_' . $user_id . '.png');
    if ($avatar_exists_png) {
        $avatar_path = $avatar_path_png;
        $avatar_exists = true;
    }
}

// Set default avatar if none exists
if (!$avatar_exists) {
    $avatar_path = "/images/default-avatar.png";
}

// Calculate account age
$created_date = new DateTime($user['created_at']);
$current_date = new DateTime();
$account_age = $created_date->diff($current_date);

// Format account age
if ($account_age->y > 0) {
    $account_age_text = $account_age->y . " year" . ($account_age->y > 1 ? "s" : "");
} elseif ($account_age->m > 0) {
    $account_age_text = $account_age->m . " month" . ($account_age->m > 1 ? "s" : "");
} elseif ($account_age->d > 0) {
    $account_age_text = $account_age->d . " day" . ($account_age->d > 1 ? "s" : "");
} else {
    $account_age_text = "Today";
}

// Get user's active subscription
$stmt = $db->prepare("
    SELECT 
        type,
        start_date,
        end_date,
        free_minutes,
        unlock_fee_waived
    FROM subscriptions
    WHERE user_id = ? AND end_date >= CURDATE()
    ORDER BY end_date DESC
    LIMIT 1
");

$stmt->execute([$user_id]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->close();

// Get user's total trips
$stmt = $db->prepare("
    SELECT COUNT(*) as total_trips
    FROM vehicle_usage
    WHERE user_id = ? AND end_time IS NOT NULL
");

$stmt->execute([$user_id]);
    $trips_data = $stmt->fetch(PDO::FETCH_ASSOC);
$total_trips = $trips_data['total_trips'];
$stmt->close();

// Get user's total distance
$stmt = $db->prepare("
    SELECT COALESCE(SUM(total_distance_km), 0) as total_distance
    FROM vehicle_usage
    WHERE user_id = ? AND end_time IS NOT NULL
");

$stmt->execute([$user_id]);
    $distance_data = $stmt->fetch(PDO::FETCH_ASSOC);
$total_distance = round($distance_data['total_distance'], 2);
$stmt->close();

// Store profile data in session for easy access
$_SESSION['profile_data'] = [
    'user' => $user,
    'avatar_path' => $avatar_path,
    'account_age' => $account_age_text,
    'subscription' => $subscription,
    'total_trips' => $total_trips,
    'total_distance' => $total_distance
];

// Return profile data as array (can be used by other scripts)
return [
    'success' => true,
    'user' => $user,
    'avatar_path' => $avatar_path,
    'account_age' => $account_age_text,
    'subscription' => $subscription,
    'stats' => [
        'total_trips' => $total_trips,
        'total_distance' => $total_distance,
        'minute_balance' => $user['minute_balance']
    ]
];
?>
