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
    $db = DatabaseMariaDB::getConnection();
    
    // Verificar si la tabla user_points existe
    $tableCheck = $db->query("SHOW TABLES LIKE 'user_points'");
    if ($tableCheck->rowCount() == 0) {
        // La tabla no existe, devolver valores por defecto
        echo json_encode([
            'success' => true,
            'points' => 0,
            'total_purchased' => 0,
            'total_spent' => 0,
            'minutes_available' => 0,
            'hours_available' => 0,
            'message' => 'Sistema EazyPoints no instal·lat. Executa setup-eazypoints.html'
        ]);
        exit;
    }
    
    // Obtener registro de puntos y estado premium
    $stmt = $db->prepare("
        SELECT up.points, up.total_purchased, up.total_spent, 
               u.is_premium, u.premium_expires_at
        FROM user_points up
        JOIN users u ON u.id = up.user_id
        WHERE up.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        // Crear registro si no existe
        $stmt = $db->prepare("INSERT INTO user_points (user_id, points) VALUES (?, 0)");
        $stmt->execute([$user_id]);
        $result = ['points' => 0, 'total_purchased' => 0, 'total_spent' => 0, 'is_premium' => false, 'premium_expires_at' => null];
    }
    
    // Calcular tiempo disponible
    // Sistema de puntos:
    // 30 min = 400 pts | 1h = 800 pts | 2h = 1600 pts
    // A partir de 2h: 1000 pts/hora adicional
    // Premium: 900 pts/hora desde la 3ra hora
    $points = (int)$result['points'];
    $minutes_available = 0;
    $is_premium = (bool)($result['is_premium'] ?? false);
    $premium_valid = false;
    
    if ($is_premium && $result['premium_expires_at']) {
        $premium_valid = strtotime($result['premium_expires_at']) > time();
    }
    
    if ($points >= 400) {
        if ($points < 800) {
            // Menos de 1 hora: proporcional hasta 30 min
            $minutes_available = floor($points / 400 * 30);
        } else if ($points < 1600) {
            // Entre 1 y 2 horas: 800 pts = 1h (60 min)
            $minutes_available = floor($points / 800 * 60);
        } else {
            // Más de 2 horas
            $minutes_available = 120; // 2 horas base
            $remaining_points = $points - 1600;
            
            if ($premium_valid) {
                // Premium: 900 pts/hora adicional
                $minutes_available += floor($remaining_points / 900 * 60);
            } else {
                // Normal: 1000 pts/hora adicional
                $minutes_available += floor($remaining_points / 1000 * 60);
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'points' => $points,
        'total_purchased' => (int)$result['total_purchased'],
        'total_spent' => (int)$result['total_spent'],
        'minutes_available' => $minutes_available,
        'hours_available' => round($minutes_available / 60, 1),
        'is_premium' => (bool)($result['is_premium'] ?? false),
        'premium_expires_at' => $result['premium_expires_at'] ?? null
    ]);
    
} catch (Exception $e) {
    error_log("Error en get-points: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage(),
        'points' => 0,
        'minutes_available' => 0,
        'hours_available' => 0
    ]);
}
?>
