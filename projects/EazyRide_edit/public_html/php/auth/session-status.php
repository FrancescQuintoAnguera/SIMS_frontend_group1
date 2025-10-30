<?php
require_once __DIR__ . '/session.php'; // utilidades de sesión
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080'); // tu front
header('Access-Control-Allow-Credentials: true');

startSecureSession();

if (isLoggedIn()) {
    echo json_encode([
        'logged_in' => true,
        'user_id' => getCurrentUserId(),
        'username' => getCurrentUsername(),
        'is_admin' => isAdmin()
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
