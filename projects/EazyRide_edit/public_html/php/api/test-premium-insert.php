<?php
require_once __DIR__ . '/../core/DatabaseMariaDB.php';

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No hay sesión activa']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $conn = DatabaseMariaDB::getConnection();
    
    // Test 1: Ver usuario actual
    $stmt = $conn->prepare("SELECT id, username, is_premium FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'user' => $user,
        'test_data' => [
            'type' => 'monthly',
            'type_length' => strlen('monthly'),
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 month')),
            'price' => 9.99
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
