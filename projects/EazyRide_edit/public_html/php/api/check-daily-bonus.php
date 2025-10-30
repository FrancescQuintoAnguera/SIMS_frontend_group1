<?php
require_once __DIR__ . '/../core/DatabaseMariaDB.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Credentials: true');

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autoritzat']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $conn = DatabaseMariaDB::getConnection();
    
    // Obtener información del usuario
    $stmt = $conn->prepare("
        SELECT is_premium, premium_expires_at, last_daily_bonus 
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        throw new Exception('Usuario no encontrado');
    }
    
    $is_premium = (bool)$result['is_premium'];
    $premium_expires_at = $result['premium_expires_at'];
    $last_daily_bonus = $result['last_daily_bonus'];
    $today = date('Y-m-d');
    
    // Verificar si es premium activo
    $premium_active = $is_premium && 
                      $premium_expires_at && 
                      strtotime($premium_expires_at) > time();
    
    // Verificar si puede reclamar bonus hoy
    $can_claim = $premium_active && ($last_daily_bonus !== $today);
    
    // Calcular próximo bonus disponible
    $next_bonus = null;
    if ($premium_active) {
        if ($last_daily_bonus === $today) {
            $next_bonus = date('Y-m-d', strtotime('+1 day'));
        } else {
            $next_bonus = $today;
        }
    }
    
    echo json_encode([
        'success' => true,
        'is_premium' => $premium_active,
        'can_claim' => $can_claim,
        'last_claimed' => $last_daily_bonus,
        'next_bonus' => $next_bonus,
        'bonus_amount' => 200,
        'premium_expires_at' => $premium_expires_at
    ]);
    
} catch (Exception $e) {
    error_log("Error en check-daily-bonus: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
