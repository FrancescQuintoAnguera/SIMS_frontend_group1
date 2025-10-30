<?php
require_once __DIR__ . '/../controllers/ProfileController.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080'); 
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Start session and check if user is authenticated
session_start();
if (!isset($_SESSION['user_id'], $_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// GET request: fetch user profile
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $profile = ProfileController::getProfile($user_id);

    if ($profile) {
        // Add username for fallback if fullname is empty
        $profile['username'] = $username;
        echo json_encode(['success' => true, 'data' => $profile]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }

// POST request: update user profile
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $allowedFields = ['fullname', 'dni', 'phone', 'birthdate', 'address', 'sex'];
    $data = [];

    // Only include allowed fields
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) $data[$field] = $input[$field];
    }

    // Update profile in database
    $updated = ProfileController::updateProfile($user_id, $data);

    if ($updated) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating profile']);
    }

// Method not allowed
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
