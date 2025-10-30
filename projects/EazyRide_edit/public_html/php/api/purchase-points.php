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

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$points = (int)$data['points'];
$price = (float)$data['price'];
$package = $data['package'];
$discount = (int)$data['discount'];

try {
    $db = DatabaseMariaDB::getConnection();
    
    // Verificar si las tablas existen
    $tableCheck = $db->query("SHOW TABLES LIKE 'user_points'");
    if ($tableCheck->rowCount() == 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Sistema EazyPoints no instal·lat. Executa setup-eazypoints.html primer.'
        ]);
        exit;
    }
    
    $db->beginTransaction();
    
    // Verificar si es usuario premium (solo si existe la columna)
    $checkColumn = $db->query("SHOW COLUMNS FROM users LIKE 'is_premium'");
    $final_price = $price;
    $final_discount = $discount;
    
    if ($checkColumn->rowCount() > 0) {
        // La columna existe, verificar premium
        $stmt = $db->prepare("
            SELECT is_premium, premium_expires_at 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Descuento adicional del 15% para premium
        if ($user && isset($user['is_premium']) && $user['is_premium'] && 
            isset($user['premium_expires_at']) && 
            strtotime($user['premium_expires_at']) > time()) {
            $final_price = $price * 0.85;  // 15% adicional
            $final_discount += 15;
        }
    }
    
    // Actualizar puntos del usuario (INSERT ... ON DUPLICATE KEY)
    $stmt = $db->prepare("
        INSERT INTO user_points (user_id, points, total_purchased) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            points = points + VALUES(points),
            total_purchased = total_purchased + VALUES(points)
    ");
    $stmt->execute([$user_id, $points, $points]);
    
    // Registrar transacción
    $description = "Compra paquet $package - $points punts";
    $stmt = $db->prepare("
        INSERT INTO point_transactions 
        (user_id, type, points, price, package_name, discount, description) 
        VALUES (?, 'purchase', ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $points, $final_price, $package, $final_discount, $description]);
    
    // Obtener saldo actualizado
    $stmt = $db->prepare("SELECT points FROM user_points WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $new_balance = (int)$row['points'];
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Compra realitzada correctament!',
        'points_added' => $points,
        'new_balance' => $new_balance,
        'price_paid' => $final_price,
        'discount_applied' => $final_discount
    ]);
    
} catch (Exception $e) {
    if ($db) {
        $db->rollback();
    }
    error_log("Error en purchase-points: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
