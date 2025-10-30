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
    $conn->beginTransaction();
    
    // Verificar si el usuario es premium y está activo
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
    
    // Verificar que es premium y está activo
    if (!$result['is_premium'] || !$result['premium_expires_at']) {
        echo json_encode([
            'success' => false, 
            'message' => 'No ets usuari Premium',
            'can_claim' => false
        ]);
        exit;
    }
    
    $expires_at = strtotime($result['premium_expires_at']);
    if ($expires_at <= time()) {
        echo json_encode([
            'success' => false, 
            'message' => 'La teva subscripció Premium ha caducat',
            'can_claim' => false
        ]);
        exit;
    }
    
    // Verificar si ya reclamó el bonus hoy
    $last_bonus = $result['last_daily_bonus'];
    $today = date('Y-m-d');
    
    if ($last_bonus === $today) {
        echo json_encode([
            'success' => false, 
            'message' => 'Ja has reclamat el bonus d\'avui. Torna demà!',
            'can_claim' => false,
            'next_bonus' => date('Y-m-d', strtotime('+1 day'))
        ]);
        exit;
    }
    
    // Otorgar bonus diario (200 puntos = 15 minutos)
    $bonus_points = 200;
    
    // Verificar si existe registro en user_points
    $checkStmt = $conn->prepare("SELECT user_id FROM user_points WHERE user_id = ?");
    $checkStmt->execute([$user_id]);
    $exists = $checkStmt->rowCount() > 0;
    
    if (!$exists) {
        $stmt = $conn->prepare("INSERT INTO user_points (user_id, points) VALUES (?, ?)");
        $stmt->execute([$user_id, $bonus_points]);
    } else {
        $stmt = $conn->prepare("UPDATE user_points SET points = points + ? WHERE user_id = ?");
        $stmt->execute([$bonus_points, $user_id]);
    }
    
    // Registrar transacción
    $description = "Bonus diari Premium - 15 minuts gratuïts";
    $stmt = $conn->prepare("
        INSERT INTO point_transactions 
        (user_id, type, points, price, description) 
        VALUES (?, 'premium_daily', ?, 0.00, ?)
    ");
    $stmt->execute([$user_id, $bonus_points, $description]);
    
    // Actualizar last_daily_bonus
    $stmt = $conn->prepare("UPDATE users SET last_daily_bonus = ? WHERE id = ?");
    $stmt->execute([$today, $user_id]);
    
    // Obtener nuevo saldo
    $stmt = $conn->prepare("SELECT points FROM user_points WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $new_balance = $stmt->fetch(PDO::FETCH_ASSOC)['points'];
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Bonus diari reclamat amb èxit!',
        'points_added' => $bonus_points,
        'new_balance' => $new_balance,
        'can_claim' => false,
        'next_bonus' => date('Y-m-d', strtotime('+1 day'))
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    error_log("Error en claim-daily-bonus: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al reclamar el bonus: ' . $e->getMessage()
    ]);
}
?>
