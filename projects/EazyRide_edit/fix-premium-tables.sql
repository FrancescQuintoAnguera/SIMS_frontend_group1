-- Corregir estructura de las tablas premium y actualizar datos

-- 1. Asegurar que last_daily_bonus existe en users
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS last_daily_bonus DATE DEFAULT NULL
COMMENT 'Última fecha de bonus diario reclamado';

-- 2. Asegurar que columnas premium existen en users
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS is_premium TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS premium_expires_at DATE DEFAULT NULL;

-- 3. Crear índice en usuarios premium activos
CREATE INDEX IF NOT EXISTS idx_premium_active ON users(is_premium, premium_expires_at);

-- 4. Asegurar que la tabla premium_subscriptions existe con la estructura correcta
CREATE TABLE IF NOT EXISTS premium_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('monthly', 'annual') NOT NULL,
    status ENUM('active', 'cancelled', 'expired') DEFAULT 'active',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    auto_renew TINYINT(1) DEFAULT 1,
    last_daily_bonus DATE DEFAULT NULL COMMENT 'Última fecha de bonus diario en esta suscripción',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_end_date (end_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Asegurar que la tabla user_points existe
CREATE TABLE IF NOT EXISTS user_points (
    user_id INT PRIMARY KEY,
    points INT NOT NULL DEFAULT 0 COMMENT 'Puntos disponibles',
    total_purchased INT NOT NULL DEFAULT 0 COMMENT 'Total de puntos comprados',
    total_spent INT NOT NULL DEFAULT 0 COMMENT 'Total de puntos gastados',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Asegurar que la tabla point_transactions existe
CREATE TABLE IF NOT EXISTS point_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('purchase', 'usage', 'refund', 'premium_bonus', 'daily_bonus') NOT NULL,
    points INT NOT NULL,
    price DECIMAL(10,2) DEFAULT 0.00,
    package_name VARCHAR(50) DEFAULT NULL,
    discount INT DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Actualizar usuarios con suscripciones activas pero sin flag premium
UPDATE users u
INNER JOIN premium_subscriptions ps ON u.id = ps.user_id
SET u.is_premium = 1,
    u.premium_expires_at = ps.end_date
WHERE ps.status = 'active' 
  AND ps.end_date >= CURDATE()
  AND (u.is_premium = 0 OR u.is_premium IS NULL);

-- 8. Desactivar premium para usuarios con suscripción expirada
UPDATE users u
SET u.is_premium = 0,
    u.premium_expires_at = NULL
WHERE u.is_premium = 1 
  AND (u.premium_expires_at < CURDATE() OR u.premium_expires_at IS NULL);

-- 9. Marcar suscripciones expiradas
UPDATE premium_subscriptions
SET status = 'expired'
WHERE end_date < CURDATE() AND status = 'active';

-- Mostrar información
SELECT 'Usuarios Premium activos:' as Info, COUNT(*) as Total 
FROM users 
WHERE is_premium = 1 AND premium_expires_at >= CURDATE();

SELECT 'Suscripciones activas:' as Info, COUNT(*) as Total 
FROM premium_subscriptions 
WHERE status = 'active' AND end_date >= CURDATE();

SELECT 'Estructura corregida correctamente!' as Resultado;
