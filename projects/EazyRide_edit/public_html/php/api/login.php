<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');


require_once __DIR__ . '/../controllers/AuthController.php';

$input = json_decode(file_get_contents("php://input"), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

echo json_encode(AuthController::login($username, $password));
