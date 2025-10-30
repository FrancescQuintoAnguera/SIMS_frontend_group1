<?php
/**
 * Script de debug para verificar bookings
 * Ejecutar desde línea de comandos o navegador
 */

require_once __DIR__ . '/public_html/php/core/DatabaseMariaDB.php';

try {
    $db = DatabaseMariaDB::getConnection();
    
    echo "=== VERIFICACIÓN DE BOOKINGS ===\n\n";
    
    // 1. Ver todos los vehículos
    echo "1. VEHÍCULOS EN LA BASE DE DATOS:\n";
    echo "-----------------------------------\n";
    $result = $db->query("SELECT id, model, plate, status, battery_level FROM vehicles");
    while ($row = $result->fetch_assoc()) {
        echo sprintf("ID: %d | %s | %s | Status: %s | Batería: %d%%\n", 
            $row['id'], 
            $row['model'], 
            $row['plate'], 
            $row['status'], 
            $row['battery_level']
        );
    }
    echo "\n";
    
    // 2. Ver todos los usuarios
    echo "2. USUARIOS EN LA BASE DE DATOS:\n";
    echo "-----------------------------------\n";
    $result = $db->query("SELECT id, username, email FROM users");
    while ($row = $result->fetch_assoc()) {
        echo sprintf("ID: %d | Username: %s | Email: %s\n", 
            $row['id'], 
            $row['username'], 
            $row['email']
        );
    }
    echo "\n";
    
    // 3. Ver todos los bookings
    echo "3. TODOS LOS BOOKINGS:\n";
    echo "-----------------------------------\n";
    $result = $db->query("
        SELECT 
            b.id,
            b.user_id,
            u.username,
            b.vehicle_id,
            v.model,
            b.status,
            b.start_datetime,
            b.end_datetime,
            b.created_at
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN vehicles v ON b.vehicle_id = v.id
        ORDER BY b.created_at DESC
        LIMIT 10
    ");
    
    if ($result->num_rows == 0) {
        echo "❌ NO HAY BOOKINGS EN LA BASE DE DATOS\n";
    } else {
        while ($row = $result->fetch_assoc()) {
            echo sprintf(
                "ID: %d | User: %s (ID: %d) | Vehicle: %s (ID: %d)\n" .
                "  Status: %s | Start: %s | End: %s | Created: %s\n\n",
                $row['id'],
                $row['username'] ?? 'N/A',
                $row['user_id'],
                $row['model'] ?? 'N/A',
                $row['vehicle_id'],
                $row['status'],
                $row['start_datetime'],
                $row['end_datetime'],
                $row['created_at']
            );
        }
    }
    
    // 4. Ver bookings activos/confirmados
    echo "4. BOOKINGS ACTIVOS/CONFIRMADOS:\n";
    echo "-----------------------------------\n";
    $result = $db->query("
        SELECT 
            b.id,
            u.username,
            v.model,
            b.status,
            b.start_datetime,
            b.end_datetime,
            NOW() as current_time,
            CASE 
                WHEN NOW() BETWEEN b.start_datetime AND b.end_datetime THEN 'EN CURSO'
                WHEN NOW() < b.start_datetime THEN 'FUTURO'
                ELSE 'PASADO'
            END as time_status
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN vehicles v ON b.vehicle_id = v.id
        WHERE b.status IN ('confirmed', 'active')
        ORDER BY b.start_datetime DESC
    ");
    
    if ($result->num_rows == 0) {
        echo "❌ NO HAY BOOKINGS ACTIVOS\n";
    } else {
        while ($row = $result->fetch_assoc()) {
            echo sprintf(
                "ID: %d | User: %s | Vehicle: %s | Status: %s\n" .
                "  Start: %s | End: %s\n" .
                "  Time Status: %s | Current: %s\n\n",
                $row['id'],
                $row['username'] ?? 'N/A',
                $row['model'] ?? 'N/A',
                $row['status'],
                $row['start_datetime'],
                $row['end_datetime'],
                $row['time_status'],
                $row['current_time']
            );
        }
    }
    
    echo "=== FIN DE LA VERIFICACIÓN ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
