-- Sistema Premium EazyRide - Actualización completa
-- Este script añade/actualiza el sistema premium con todas las columnas necesarias

-- 1. Añadir columnas is_premium y premium_expires_at a la tabla users si no existen
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS is_premium BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS premium_expires_at DATETIME DEFAULT NULL;

-- 2. Crear índice para búsquedas rápidas
CREATE INDEX IF NOT EXISTS idx_users_premium ON users(is_premium, premium_expires_at);

-- 3. Crear tabla de suscripciones premium si no existe
CREATE TABLE IF NOT EXISTS premium_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('monthly', 'annual') NOT NULL,
    status ENUM('active', 'cancelled', 'expired') DEFAULT 'active',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    auto_renew BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Actualizar tabla user_points para añadir campos relacionados con premium
ALTER TABLE user_points 
ADD COLUMN IF NOT EXISTS daily_bonus_claimed_at DATE DEFAULT NULL,
ADD COLUMN IF NOT EXISTS total_bonus_received INT DEFAULT 0;

-- 5. Verificar y crear tabla point_transactions si no existe
CREATE TABLE IF NOT EXISTS point_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('purchase', 'spend', 'bonus', 'premium_daily', 'refund') NOT NULL,
    points INT NOT NULL,
    price DECIMAL(10, 2) DEFAULT NULL,
    package_name VARCHAR(50) DEFAULT NULL,
    discount INT DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Actualizar configuración de precios
INSERT INTO system_config (config_key, config_value, description) VALUES
('premium_monthly_price', '9.99', 'Precio mensual subscripción Premium'),
('premium_annual_price', '95.00', 'Precio anual subscripción Premium'),
('premium_discount_percentage', '15', 'Descuento adicional para usuarios Premium'),
('premium_daily_bonus_points', '200', 'Puntos diarios gratuitos para Premium (15 min)'),
('points_per_30min', '400', 'Puntos necesarios para 30 minutos de alquiler'),
('points_per_hour', '800', 'Puntos necesarios para 1 hora de alquiler'),
('points_per_2hours', '1600', 'Puntos necesarios para 2 horas de alquiler'),
('points_per_additional_hour', '1000', 'Puntos por cada hora adicional después de 2h')
ON DUPLICATE KEY UPDATE 
    config_value = VALUES(config_value),
    description = VALUES(description);

-- 7. Crear tabla de configuración si no existe
CREATE TABLE IF NOT EXISTS system_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT NOT NULL,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Procedimiento para reclamar bonus diario Premium
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS claim_premium_daily_bonus(IN p_user_id INT)
BEGIN
    DECLARE v_is_premium BOOLEAN;
    DECLARE v_premium_expires DATETIME;
    DECLARE v_last_claim DATE;
    DECLARE v_bonus_points INT DEFAULT 200;
    DECLARE v_today DATE;
    
    SET v_today = CURDATE();
    
    -- Verificar si el usuario es premium y está activo
    SELECT is_premium, premium_expires_at INTO v_is_premium, v_premium_expires
    FROM users WHERE id = p_user_id;
    
    IF v_is_premium AND v_premium_expires > NOW() THEN
        -- Verificar si ya reclamó hoy
        SELECT daily_bonus_claimed_at INTO v_last_claim
        FROM user_points WHERE user_id = p_user_id;
        
        IF v_last_claim IS NULL OR v_last_claim < v_today THEN
            -- Añadir puntos
            UPDATE user_points 
            SET points = points + v_bonus_points,
                total_bonus_received = total_bonus_received + v_bonus_points,
                daily_bonus_claimed_at = v_today
            WHERE user_id = p_user_id;
            
            -- Registrar transacción
            INSERT INTO point_transactions (user_id, type, points, description)
            VALUES (p_user_id, 'premium_daily', v_bonus_points, 'Bonus diari Premium - 15 minuts gratuïts');
            
            SELECT TRUE as success, v_bonus_points as points_added, 'Bonus reclamat!' as message;
        ELSE
            SELECT FALSE as success, 0 as points_added, 'Ja has reclamat el bonus avui' as message;
        END IF;
    ELSE
        SELECT FALSE as success, 0 as points_added, 'No ets usuari Premium o la subscripció ha caducat' as message;
    END IF;
END//

DELIMITER ;

-- 9. Evento para expirar suscripciones automáticamente
DROP EVENT IF EXISTS expire_premium_subscriptions;

CREATE EVENT IF NOT EXISTS expire_premium_subscriptions
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- Actualizar usuarios cuya subscripción ha expirado
    UPDATE users 
    SET is_premium = FALSE 
    WHERE is_premium = TRUE 
    AND premium_expires_at < NOW();
    
    -- Actualizar estado de suscripciones
    UPDATE premium_subscriptions 
    SET status = 'expired' 
    WHERE status = 'active' 
    AND end_date < CURDATE();
END;

-- 10. Activar el scheduler de eventos
SET GLOBAL event_scheduler = ON;

-- Mensaje de éxito
SELECT '✅ Sistema Premium instalado correctamente' as status,
       'Las tablas han sido creadas/actualizadas' as message,
       'Procedimientos y eventos configurados' as details;
