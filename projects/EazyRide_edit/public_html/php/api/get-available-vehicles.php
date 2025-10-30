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

try {
    $db = DatabaseMariaDB::getConnection();
    
    // Obtener vehículos disponibles (no en mantenimiento ni reservados activamente)
    $stmt = $db->prepare("
        SELECT 
            v.id,
            v.model,
            v.license_plate,
            v.status,
            v.battery_level,
            v.latitude,
            v.longitude,
            v.price_per_minute
        FROM vehicles v
        WHERE v.status IN ('available', 'reserved')
        AND v.battery_level >= 20
        ORDER BY v.battery_level DESC, v.id ASC
    ");
    
    $stmt->execute();
    // PDO: result is in stmt directly
    
    $vehicles = [];
    while ($row = $result->fetch_assoc()) {
        // Calcular distancia aproximada (simulada - en producción usar geolocalización real)
        $distance = round(rand(5, 25) / 10, 1) . ' km';
        
        $vehicles[] = [
            'id' => (int)$row['id'],
            'model' => $row['model'],
            'license_plate' => $row['license_plate'],
            'status' => $row['status'],
            'battery_level' => (int)$row['battery_level'],
            'latitude' => $row['latitude'],
            'longitude' => $row['longitude'],
            'price_per_minute' => (float)$row['price_per_minute'],
            'distance' => $distance
        ];
    }
    
    echo json_encode([
        'success' => true,
        'vehicles' => $vehicles,
        'count' => count($vehicles)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtenir vehicles',
        'error' => $e->getMessage()
    ]);
}
?>
