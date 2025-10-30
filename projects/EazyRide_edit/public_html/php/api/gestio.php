<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'message'=>'User not logged in']);
    exit();
}

require_once __DIR__ . '/../controllers/ProfileController.php';
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

$user_info = ProfileController::getUserInfo($user_id);

if ($user_info) {
    echo json_encode([
        "success" => true,
        "user" => [
            "id" => $user_id,
            "username" => $user_info['username'],
            "email" => $user_info['email'],
            "minute_balance" => $user_info['minute_balance'],
            "is_admin" => $user_info['is_admin']
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);
}