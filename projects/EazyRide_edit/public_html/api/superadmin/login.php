<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

try {
    $tenantManager = getTenantManager();
    $admin = $tenantManager->authenticateTenantAdmin($username, $password);

    if ($admin) {
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'admin' => [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'email' => $admin['email'],
                'fullname' => $admin['fullname'],
                'is_super_admin' => $admin['is_super_admin']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Login error: ' . $e->getMessage()
    ]);
}
