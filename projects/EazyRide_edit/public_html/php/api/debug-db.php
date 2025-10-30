<?php
/**
 * Debug script to check database connection and vehicles data
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Credentials: true');

require_once __DIR__ . '/../core/DatabaseMariaDB.php';

try {
    $db = DatabaseMariaDB::getConnection();
    
    // Check connection
    $connectionInfo = [
        'connected' => true,
        'server_info' => $db->server_info,
        'host_info' => $db->host_info,
        'protocol_version' => $db->protocol_version
    ];
    
    // Count vehicles
    $result = $db->query("SELECT COUNT(*) as total FROM vehicles");
    $row = $result->fetch_assoc();
    $vehicleCount = $row['total'];
    
    // Get all vehicles
    $result = $db->query("SELECT * FROM vehicles LIMIT 10");
    $vehicles = [];
    while ($row = $result->fetch_assoc()) {
        $vehicles[] = $row;
    }
    
    // Check if tables exist
    $tables = [];
    $result = $db->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    echo json_encode([
        'success' => true,
        'connection' => $connectionInfo,
        'database' => 'simsdb',
        'tables' => $tables,
        'vehicle_count' => $vehicleCount,
        'vehicles' => $vehicles
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>
