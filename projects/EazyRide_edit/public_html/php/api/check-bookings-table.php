<?php
/**
 * Verify and create bookings table if needed
 */

require_once __DIR__ . '/../core/DatabaseMariaDB.php';

header('Content-Type: application/json');

try {
    $db = DatabaseMariaDB::getConnection();
    
    $result = [
        'table_exists' => false,
        'table_structure' => null,
        'creation_attempted' => false,
        'errors' => []
    ];
    
    // Check if table exists
    $checkTable = $db->query("SHOW TABLES LIKE 'bookings'");
    $result['table_exists'] = $checkTable->rowCount() > 0;
    
    if ($result['table_exists']) {
        // Get table structure
        $structure = $db->query("DESCRIBE bookings");
        $result['table_structure'] = $structure->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Create table
        $result['creation_attempted'] = true;
        
        $createSQL = "
        CREATE TABLE IF NOT EXISTS bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            vehicle_id INT NOT NULL,
            start_datetime DATETIME NOT NULL,
            end_datetime DATETIME NOT NULL,
            status ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled') DEFAULT 'pending',
            total_minutes INT,
            total_cost DECIMAL(10,2),
            pickup_location VARCHAR(255),
            dropoff_location VARCHAR(255),
            notes TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_vehicle_id (vehicle_id),
            INDEX idx_status (status),
            INDEX idx_datetime (start_datetime, end_datetime),
            INDEX idx_user_vehicle (user_id, vehicle_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        if ($db->query($createSQL)) {
            $result['table_created'] = true;
            
            // Get structure of newly created table
            $structure = $db->query("DESCRIBE bookings");
            $result['table_structure'] = $structure->fetchAll(PDO::FETCH_ASSOC);
            $result['table_exists'] = true;
        } else {
            $result['table_created'] = false;
            $result['errors'][] = $db->error;
        }
    }
    
    // Test insert
    $result['test_insert'] = false;
    $testSQL = "
        INSERT INTO bookings (user_id, vehicle_id, start_datetime, end_datetime, total_cost, status)
        VALUES (1, 1, NOW(), DATE_ADD(NOW(), INTERVAL 2 HOUR), 0.50, 'active')
    ";
    
    if ($db->query($testSQL)) {
        $result['test_insert'] = true;
        $result['test_booking_id'] = $db->lastInsertId();
        
        // Delete test booking
        $db->query("DELETE FROM bookings WHERE id = " . $db->lastInsertId());
    } else {
        $result['test_insert_error'] = $db->error;
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
