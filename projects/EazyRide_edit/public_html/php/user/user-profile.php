<?php
/**
 * User Profile API Endpoint
 * Returns user data as JSON for frontend consumption
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth/session.php';

// Start session and check authentication
startSecureSession();

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please log in.'
    ]);
    exit;
}

$user_id = getCurrentUserId();

// Check if specific user ID is requested (admin only)
$requested_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $user_id;

// Only allow admins to view other users' profiles
if ($requested_user_id !== $user_id && !isAdmin()) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden. You do not have permission to view this profile.'
    ]);
    exit;
}

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
        u.nationality_id,
        n.name as nationality
    FROM users u
    LEFT JOIN nationalities n ON u.nationality_id = n.id
    WHERE u.id = ?
");

$stmt->execute([$requested_user_id]);

if ($result->rowCount() === 0) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'User not found'
    ]);
    $stmt->close();
    exit;
}

$user = $result->fetch(PDO::FETCH_ASSOC);
$stmt->close();

// Remove sensitive data if viewing another user's profile
if ($requested_user_id !== $user_id) {
    unset($user['iban']);
    unset($user['driver_license_photo']);
}

// Check if user has an avatar
$avatar_path = null;
$avatar_file_jpg = __DIR__ . '/../../images/avatars/user_' . $requested_user_id . '.jpg';
$avatar_file_png = __DIR__ . '/../../images/avatars/user_' . $requested_user_id . '.png';

if (file_exists($avatar_file_jpg)) {
    $avatar_path = "/images/avatars/user_{$requested_user_id}.jpg";
} elseif (file_exists($avatar_file_png)) {
    $avatar_path = "/images/avatars/user_{$requested_user_id}.png";
}

// Calculate account age
$created_date = new DateTime($user['created_at']);
$current_date = new DateTime();
$account_age_days = $created_date->diff($current_date)->days;

// Get user's active subscription
$stmt = $db->prepare("
    SELECT 
        id,
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

$stmt->execute([$requested_user_id]);
$subscription = $subscription_result->rowCount() > 0 ? $subscription_result->fetch(PDO::FETCH_ASSOC) : null;
$stmt->close();

// Get user's statistics
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_trips,
        COALESCE(SUM(total_distance_km), 0) as total_distance,
        COALESCE(SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)), 0) as total_minutes
    FROM vehicle_usage
    WHERE user_id = ? AND end_time IS NOT NULL
");

$stmt->execute([$requested_user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->close();

// Get user's recent trips (last 5)
$stmt = $db->prepare("
    SELECT 
        vu.id,
        vu.start_time,
        vu.end_time,
        vu.total_distance_km,
        v.brand,
        v.model,
        v.plate,
        sl.name as start_location,
        el.name as end_location
    FROM vehicle_usage vu
    JOIN vehicles v ON vu.vehicle_id = v.id
    LEFT JOIN locations sl ON vu.start_location_id = sl.id
    LEFT JOIN locations el ON vu.end_location_id = el.id
    WHERE vu.user_id = ? AND vu.end_time IS NOT NULL
    ORDER BY vu.start_time DESC
    LIMIT 5
");

$stmt->execute([$requested_user_id]);
$recent_trips = [];
while ($trip = $trips_result->fetch(PDO::FETCH_ASSOC)) {
    $recent_trips[] = $trip;
}
$stmt->close();

// Get all nationalities (for profile editing)
$nationalities = [];
$result = $db->query("SELECT id, name FROM nationalities ORDER BY name ASC");
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $nationalities[] = $row;
}

// Build response
$response = [
    'success' => true,
    'user' => [
        'id' => $user['id'],
        'email' => $user['email'],
        'username' => $user['username'],
        'fullname' => $user['fullname'],
        'phone' => $user['phone'],
        'birth_date' => $user['birth_date'],
        'is_admin' => (bool)$user['is_admin'],
        'iban' => $user['iban'] ?? null,
        'driver_license_photo' => $user['driver_license_photo'] ?? null,
        'minute_balance' => (int)$user['minute_balance'],
        'created_at' => $user['created_at'],
        'nationality_id' => $user['nationality_id'],
        'nationality' => $user['nationality'],
        'avatar_url' => $avatar_path,
        'account_age_days' => $account_age_days
    ],
    'subscription' => $subscription,
    'statistics' => [
        'total_trips' => (int)$stats['total_trips'],
        'total_distance' => round((float)$stats['total_distance'], 2),
        'total_minutes' => (int)$stats['total_minutes']
    ],
    'recent_trips' => $recent_trips,
    'nationalities' => $nationalities
];

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
?>
