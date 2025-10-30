<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autoritzat']);
    exit;
}

$db = Database::getInstance();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

try {
    // Obtener estado premium del usuario
    $stmt = $conn->prepare("SELECT is_premium, premium_expires_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $result->fetch_assoc();
    
    $is_premium = (bool)$user['is_premium'];
    $expires_at = $user['premium_expires_at'];
    
    // Si es premium, obtener detalles de la suscripción
    $subscription = null;
    if ($is_premium) {
        $stmt = $conn->prepare("
            SELECT type, status, start_date, end_date, price, auto_renew, last_daily_bonus
            FROM premium_subscriptions 
            WHERE user_id = ? AND status = 'active'
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        $subscription = $result->fetch_assoc();
    }
    
    echo json_encode([
        'success' => true,
        'is_premium' => $is_premium,
        'premium_expires_at' => $expires_at,
        'subscription' => $subscription
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtenir estat Premium',
        'error' => $e->getMessage()
    ]);
}
?>
