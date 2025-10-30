<?php
/**
 * Login Handler
 * Secure login with password verification and session management
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

// Validate input
if (!isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing username or password']);
    exit;
}

$username = trim($input['username']);
$password = $input['password'];

// Validate not empty
if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

// Attempt login
$result = AuthController::login($username, $password);

// Set appropriate HTTP status code
if ($result['success']) {
    http_response_code(200);
} else {
    http_response_code(401);
}

echo json_encode($result);
?>