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
    
    // Verificar si el usuario tiene una reserva activa
    $stmt = $conn->prepare("
        SELECT 
            b.id,
            b.vehicle_id,
            b.start_time,
            b.end_time,
            b.status,
            v.matricula,
            v.model,
            v.color
        FROM bookings b
        JOIN vehicles v ON b.vehicle_id = v.id
        WHERE b.user_id = ?
        AND b.status IN ('active', 'in_progress')
        AND b.end_time > NOW()
        ORDER BY b.start_time DESC
        LIMIT 1
    ");
    
    $stmt->execute([$user_id]);
    
    if ($result->rowCount() > 0) {
        $booking = $result->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'has_booking' => true,
            'booking' => [
                'id' => $booking['id'],
                'vehicle_id' => $booking['vehicle_id'],
                'matricula' => $booking['matricula'],
                'model' => $booking['model'],
                'color' => $booking['color'],
                'start_time' => $booking['start_time'],
                'end_time' => $booking['end_time'],
                'status' => $booking['status']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'has_booking' => false
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en get-booking-status: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
