-- Script SQL para implementar el sistema EazyPoints
-- Ejecutar en el orden indicado

-- 1. Tabla de puntos de usuario
CREATE TABLE IF NOT EXISTS user_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    points INT DEFAULT 0,
    total_purchased INT DEFAULT 0,
    total_spent INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de transacciones de puntos
CREATE TABLE IF NOT EXISTS point_transactions (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla de suscripciones premium
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

-- 4. Actualizar tabla users (si no tiene ya estos campos)
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS is_premium BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS premium_expires_at DATE DEFAULT NULL;

-- 5. Inicializar puntos para usuarios existentes
INSERT INTO user_points (user_id, points)
SELECT id, 0 FROM users
WHERE id NOT IN (SELECT user_id FROM user_points);
