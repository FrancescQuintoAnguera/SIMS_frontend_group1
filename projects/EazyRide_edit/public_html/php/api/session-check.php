<?php
/**
 * Session Check Endpoint
 * Verifica si el usuario tiene una sesión activa
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verificar si hay sesión activa
$loggedIn = isset($_SESSION['user_id']) && isset($_SESSION['username']);

if ($loggedIn) {
    echo json_encode([
        'success' => true,
        'loggedIn' => true,
        'username' => $_SESSION['username'],
        'user_id' => $_SESSION['user_id']
    ]);
} else {
    echo json_encode([
        'success' => true,
        'loggedIn' => false,
        'username' => null
    ]);
}
?>
