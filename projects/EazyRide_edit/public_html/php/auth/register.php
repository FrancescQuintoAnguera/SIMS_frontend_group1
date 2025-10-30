<?php
/**
 * Registration Handler
 * Secure user registration with validation and password hashing
 */

session_start();
require_once __DIR__ . '/../controllers/AuthController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get input data
$input = json_decode(file_get_contents("php://input"), true);

// Validate required fields
$required_fields = ['username', 'email', 'password'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty(trim($input[$field]))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Validate email format
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate password strength (minimum 6 characters)
if (strlen($input['password']) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Sanitize input
$data = [
    'username' => trim($input['username']),
    'email' => trim($input['email']),
    'password' => $input['password'],
    'nationality_id' => isset($input['nationality_id']) ? trim($input['nationality_id']) : null,
    'phone' => isset($input['phone']) ? trim($input['phone']) : null,
    'fecha_nacimiento' => isset($input['birth_date']) ? trim($input['birth_date']) : null,
    'iban' => isset($input['iban']) ? trim($input['iban']) : null,
    'driver_license_photo' => isset($input['driver_license_photo']) ? trim($input['driver_license_photo']) : null
];

// Attempt registration
$result = AuthController::register($data);

// Set appropriate HTTP status code
if ($result['success']) {
    http_response_code(201);
} else {
    http_response_code(400);
}

echo json_encode($result);
?>