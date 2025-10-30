-- Script de actualización para agregar sistema Premium y EazyPoints
-- Ejecutar este script para actualizar la base de datos

-- 1. Agregar columnas de premium a la tabla users
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS is_premium BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS premium_expires_at DATE DEFAULT NULL,
ADD COLUMN IF NOT EXISTS last_daily_bonus DATE DEFAULT NULL;

-- 2. Crear índice para consultas de premium
ALTER TABLE users
ADD INDEX IF NOT EXISTS idx_is_premium (is_premium);

-- 3. Tabla de puntos de usuario (si no existe)
CREATE TABLE IF NOT EXISTS user_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    points INT DEFAULT 0,
    total_purchased INT DEFAULT 0,
    total_spent INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id),
    INDEX idx_points (points)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla de transacciones de puntos (si no existe)
CREATE TABLE IF NOT EXISTS point_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('purchase', 'spend', 'bonus', 'refund', 'premium_daily', 'premium_bonus') NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabla de suscripciones premium (si no existe)
CREATE TABLE IF NOT EXISTS premium_subscriptions (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Inicializar puntos para usuarios existentes
INSERT IGNORE INTO user_points (user_id, points)
SELECT id, 0 FROM users
WHERE id NOT IN (SELECT user_id FROM user_points);

-- 7. Verificar que todo se creó correctamente
SELECT 
    'users' as tabla,
    COUNT(*) as filas,
    'Columnas premium agregadas' as nota
FROM users
UNION ALL
SELECT 
    'user_points' as tabla,
    COUNT(*) as filas,
    'Sistema de puntos activo' as nota
FROM user_points
UNION ALL
SELECT 
    'point_transactions' as tabla,
    COUNT(*) as filas,
    'Historial de transacciones' as nota
FROM point_transactions
UNION ALL
SELECT 
    'premium_subscriptions' as tabla,
    COUNT(*) as filas,
    'Sistema premium activo' as nota
FROM premium_subscriptions;
