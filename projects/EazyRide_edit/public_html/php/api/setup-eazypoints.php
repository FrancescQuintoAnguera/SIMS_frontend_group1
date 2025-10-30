<?php
require_once __DIR__ . '/../core/DatabaseMariaDB.php';

header('Content-Type: application/json');

try {
    $db = DatabaseMariaDB::getConnection();
    
    $results = [];
    
    // 1. Crear tabla user_points
    $sql1 = "CREATE TABLE IF NOT EXISTS user_points (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        points INT DEFAULT 0,
        total_purchased INT DEFAULT 0,
        total_spent INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($db->query($sql1)) {
        $results[] = "✓ Tabla user_points creada/verificada";
    } else {
        $results[] = "✗ Error en user_points: " . $db->error;
    }
    
    // 2. Crear tabla point_transactions
    $sql2 = "CREATE TABLE IF NOT EXISTS point_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('purchase', 'spend', 'bonus', 'refund', 'premium_daily') NOT NULL,
        points INT NOT NULL,
        price DECIMAL(10,2) DEFAULT NULL,
        package_name VARCHAR(50) DEFAULT NULL,
        discount INT DEFAULT 0,
        description TEXT,
        booking_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_type (user_id, type),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($db->query($sql2)) {
        $results[] = "✓ Tabla point_transactions creada/verificada";
    } else {
        $results[] = "✗ Error en point_transactions: " . $db->error;
    }
    
    // 3. Crear tabla premium_subscriptions
    $sql3 = "CREATE TABLE IF NOT EXISTS premium_subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('monthly', 'annual') NOT NULL,
        status ENUM('active', 'cancelled', 'expired') DEFAULT 'active',
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        auto_renew BOOLEAN DEFAULT TRUE,
        last_daily_bonus DATE DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_status (user_id, status),
        INDEX idx_end_date (end_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($db->query($sql3)) {
        $results[] = "✓ Tabla premium_subscriptions creada/verificada";
    } else {
        $results[] = "✗ Error en premium_subscriptions: " . $db->error;
    }
    
    // 4. Actualizar tabla users (agregar columnas si no existen)
    $check_columns = $db->query("SHOW COLUMNS FROM users LIKE 'is_premium'");
    if ($check_columns->num_rows == 0) {
        $sql4 = "ALTER TABLE users 
                 ADD COLUMN is_premium BOOLEAN DEFAULT FALSE,
                 ADD COLUMN premium_expires_at DATE DEFAULT NULL";
        if ($db->query($sql4)) {
            $results[] = "✓ Columnas premium agregadas a users";
        } else {
            $results[] = "✗ Error al agregar columnas: " . $db->error;
        }
    } else {
        $results[] = "✓ Columnas premium ya existen en users";
    }
    
    // 5. Inicializar puntos para usuarios existentes
    $sql5 = "INSERT IGNORE INTO user_points (user_id, points)
             SELECT id, 0 FROM users";
    if ($db->query($sql5)) {
        $affected = $db->rowCount();
        $results[] = "✓ Inicializados puntos para $affected usuarios";
    } else {
        $results[] = "✗ Error al inicializar puntos: " . $db->error;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Sistema EazyPoints instalado correctamente',
        'details' => $results
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la instalación',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
