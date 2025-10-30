<?php
header('Content-Type: application/json');
session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No autenticat'
    ]);
    exit;
}

require_once __DIR__ . '/../core/DatabaseMariaDB.php';

// Obtener datos del request
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['vehicle_id']) || !isset($input['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Dades incompletes'
    ]);
    exit;
}

$vehicleId = intval($input['vehicle_id']);
$action = $input['action'];
$userId = $_SESSION['user_id'];

try {
    $db = DatabaseMariaDB::getConnection();
    
    // Verificar que el usuario tiene una reserva activa de este vehículo
    $stmt = $db->prepare("
        SELECT id as booking_id 
        FROM bookings 
        WHERE user_id = ?
        AND vehicle_id = ?
        AND status IN ('active', 'confirmed')
        LIMIT 1
    ");
    
    $stmt->execute([$userId, $vehicleId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        echo json_encode([
            'success' => false,
            'message' => 'No tens permís per controlar aquest vehicle'
        ]);
        exit;
    }
    
    // Registrar la acción en un log (opcional - crear tabla si no existe)
    try {
        $stmt = $db->prepare("
            INSERT INTO vehicle_logs (vehicle_id, user_id, action, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$vehicleId, $userId, $action]);
    } catch (Exception $e) {
        // Si la tabla no existe, continuamos sin registrar el log
    }
    
    // Mensajes según la acción
    $messages = [
        'lock' => 'Vehicle bloquejat correctament',
        'unlock' => 'Vehicle desbloquejat correctament',
        'toggle_lights' => 'Llums activats',
        'horn' => 'Claxon activat'
    ];
    
    echo json_encode([
        'success' => true,
        'message' => $messages[$action] ?? 'Acció executada',
        'action' => $action,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al executar l\'acció',
        'error' => $e->getMessage()
    ]);
}
?>
