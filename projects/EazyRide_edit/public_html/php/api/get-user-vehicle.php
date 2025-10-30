<?php
header('Content-Type: application/json');
session_start();

error_log("=== GET USER VEHICLE DEBUG ===");
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No autenticat',
        'debug' => 'Session not set'
    ]);
    exit;
}

require_once __DIR__ . '/../core/DatabaseMariaDB.php';

try {
    $userId = $_SESSION['user_id'];
    $db = DatabaseMariaDB::getConnection();
    
    error_log("User ID: " . $userId);
    
    // PRIMERA BÚSQUEDA: Booking activo AHORA
    $query1 = "
        SELECT 
            v.id as vehicle_id,
            v.model,
            v.plate,
            v.status,
            v.battery_level,
            v.latitude,
            v.longitude,
            b.id as booking_id,
            b.start_datetime as start_time,
            b.end_datetime as end_time,
            b.status as booking_status
        FROM vehicles v
        INNER JOIN bookings b ON v.id = b.vehicle_id
        WHERE b.user_id = ?
        AND b.status IN ('confirmed', 'active')
        AND NOW() BETWEEN b.start_datetime AND b.end_datetime
        ORDER BY b.start_datetime DESC
        LIMIT 1
    ";
    
    error_log("Query 1: " . $query1);
    
    $stmt = $db->prepare($query1);
    $stmt->execute([$userId]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Query 1 result: " . ($vehicle ? json_encode($vehicle) : 'NULL'));
    
    if ($vehicle) {
        if ($vehicle['booking_status'] === 'confirmed') {
            error_log("Updating booking to active: " . $vehicle['booking_id']);
            $updateStmt = $db->prepare("UPDATE bookings SET status = 'active' WHERE id = ?");
            $updateStmt->execute([$vehicle['booking_id']]);
            $vehicle['booking_status'] = 'active';
        }
        
        $response = [
            'success' => true,
            'vehicle' => [
                'vehicle_id' => (int)$vehicle['vehicle_id'],
                'model' => $vehicle['model'],
                'plate' => $vehicle['plate'],
                'status' => $vehicle['status'],
                'battery_level' => (int)$vehicle['battery_level'],
                'latitude' => $vehicle['latitude'],
                'longitude' => $vehicle['longitude'],
                'speed' => 0,
                'start_time' => $vehicle['start_time'],
                'end_time' => $vehicle['end_time'],
                'booking_status' => $vehicle['booking_status']
            ]
        ];
        
        error_log("Returning success: " . json_encode($response));
        echo json_encode($response);
        exit;
    }
    
    error_log("Query 1 found nothing, trying query 2...");
    
    // SEGUNDA BÚSQUEDA: Reservas próximas (dentro de 3 horas) o que ya deberían estar activas
    $query2 = "
        SELECT 
            v.id as vehicle_id,
            v.model,
            v.plate,
            v.status,
            v.battery_level,
            v.latitude,
            v.longitude,
            b.id as booking_id,
            b.start_datetime as start_time,
            b.end_datetime as end_time,
            b.status as booking_status
        FROM vehicles v
        INNER JOIN bookings b ON v.id = b.vehicle_id
        WHERE b.user_id = ?
        AND b.status IN ('confirmed', 'active')
        AND b.start_datetime <= DATE_ADD(NOW(), INTERVAL 3 HOUR)
        AND b.end_datetime >= NOW()
        ORDER BY b.start_datetime ASC
        LIMIT 1
    ";
    
    error_log("Query 2: " . $query2);
    
    $stmt = $db->prepare($query2);
    $stmt->execute([$userId]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Query 2 result: " . ($vehicle ? json_encode($vehicle) : 'NULL'));
    
    if ($vehicle) {
        $response = [
            'success' => true,
            'vehicle' => [
                'vehicle_id' => (int)$vehicle['vehicle_id'],
                'model' => $vehicle['model'],
                'plate' => $vehicle['plate'],
                'status' => $vehicle['status'],
                'battery_level' => (int)$vehicle['battery_level'],
                'latitude' => $vehicle['latitude'],
                'longitude' => $vehicle['longitude'],
                'speed' => 0,
                'start_time' => $vehicle['start_time'],
                'end_time' => $vehicle['end_time'],
                'booking_status' => $vehicle['booking_status'],
                'is_upcoming' => true
            ]
        ];
        
        error_log("Returning success (upcoming): " . json_encode($response));
        echo json_encode($response);
    } else {
        error_log("No vehicle found for user");
        
        // Debug: Mostrar todos los bookings del usuario
        $debugStmt = $db->prepare("
            SELECT b.id, b.status, b.start_datetime, b.end_datetime, v.model
            FROM bookings b
            LEFT JOIN vehicles v ON b.vehicle_id = v.id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC
            LIMIT 5
        ");
        $debugStmt->execute([$userId]);
        $allBookings = [];
        while ($row = $debugResult->fetch(PDO::FETCH_ASSOC)) {
            $allBookings[] = $row;
        }
        error_log("All user bookings: " . json_encode($allBookings));
        
        echo json_encode([
            'success' => false,
            'message' => 'No tens cap vehicle reservat',
            'debug' => [
                'user_id' => $userId,
                'current_time' => date('Y-m-d H:i:s'),
                'all_bookings' => $allBookings
            ]
        ]);
    }
    
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtenir les dades del vehicle',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
