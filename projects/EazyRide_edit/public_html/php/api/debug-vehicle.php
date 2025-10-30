<?php
/**
 * Debug endpoint to check vehicle claim status
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../core/DatabaseMariaDB.php';

try {
    $db = DatabaseMariaDB::getConnection();
    $userId = $_SESSION['user_id'] ?? null;
    
    $debug = [
        'session_user_id' => $userId,
        'session_vehicle_id' => $_SESSION['current_vehicle_id'] ?? null,
        'session_data' => $_SESSION,
        'bookings_table_exists' => false,
        'bookings' => [],
        'all_bookings' => [],
        'vehicles_in_use' => [],
        'last_error' => null
    ];
    
    // Check if bookings table exists
    $checkTable = $db->query("SHOW TABLES LIKE 'bookings'");
    $debug['bookings_table_exists'] = $checkTable->rowCount() > 0;
    
    if (!$debug['bookings_table_exists']) {
        $debug['error'] = 'Bookings table does not exist! Run check-bookings-table.php to create it.';
    }
    
    if ($userId && $debug['bookings_table_exists']) {
        // Buscar bookings del usuario
        $stmt = $db->prepare("
            SELECT b.*, v.plate, v.model, v.brand, v.status as vehicle_status
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            WHERE b.user_id = ?
            ORDER BY b.start_datetime DESC
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $debug['bookings'] = $result->fetchAll(PDO::FETCH_ASSOC);
        
        // Todos los bookings (para debug)
        $allBookings = $db->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 10");
        $debug['all_bookings'] = $allBookings->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar vehículos en uso
        $stmt2 = $db->prepare("
            SELECT id, plate, model, brand, status
            FROM vehicles
            WHERE status = 'in_use'
        ");
        $stmt2->execute();
        // $result2 = $stmt2; // PDO: stmt ya contiene resultados
        $debug['vehicles_in_use'] = $result2->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get last MySQL error
    if ($db->error) {
        $debug['last_error'] = $db->error;
    }
    
    echo json_encode($debug, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
