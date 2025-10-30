-- =====================================================
-- SCRIPT DE INSTALACIÓN COMPLETA DEL SISTEMA
-- EazyRide - Premium y EazyPoints
-- =====================================================

-- Desactivar verificación de foreign keys temporalmente
SET FOREIGN_KEY_CHECKS = 0;

-- 1. ACTUALIZAR TABLA USERS CON COLUMNAS PREMIUM
-- =====================================================
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS is_premium BOOLEAN DEFAULT FALSE COMMENT 'Usuario premium activo',
ADD COLUMN IF NOT EXISTS premium_expires_at DATE DEFAULT NULL COMMENT 'Fecha de expiración premium';

-- Crear índice para consultas rápidas
CREATE INDEX IF NOT EXISTS idx_is_premium ON users(is_premium);
CREATE INDEX IF NOT EXISTS idx_premium_expires ON users(premium_expires_at);

-- 2. CREAR TABLA DE PUNTOS DE USUARIO
-- =====================================================
CREATE TABLE IF NOT EXISTS user_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    points INT DEFAULT 0 COMMENT 'Puntos actuales disponibles',
    total_purchased INT DEFAULT 0 COMMENT 'Total de puntos comprados',
    total_spent INT DEFAULT 0 COMMENT 'Total de puntos gastados',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id),
    INDEX idx_points (points),
    INDEX idx_user_points (user_id, points)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Saldo de puntos por usuario';

-- 3. CREAR TABLA DE TRANSACCIONES DE PUNTOS
-- =====================================================
CREATE TABLE IF NOT EXISTS point_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('purchase', 'spend', 'bonus', 'refund', 'premium_daily') NOT NULL COMMENT 'Tipo de transacción',
    points INT NOT NULL COMMENT 'Cantidad de puntos (positivo o negativo)',
    price DECIMAL(10,2) DEFAULT NULL COMMENT 'Precio pagado en euros',
    package_name VARCHAR(50) DEFAULT NULL COMMENT 'Nombre del paquete comprado',
    discount INT DEFAULT 0 COMMENT 'Porcentaje de descuento aplicado',
    description TEXT COMMENT 'Descripción de la transacción',
    booking_id INT DEFAULT NULL COMMENT 'ID de reserva si aplica',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, type),
    INDEX idx_created (created_at),
    INDEX idx_user_date (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial de transacciones de puntos';

-- 4. CREAR TABLA DE SUSCRIPCIONES PREMIUM
-- =====================================================
CREATE TABLE IF NOT EXISTS premium_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('monthly', 'annual') NOT NULL COMMENT 'Tipo de suscripción',
    status ENUM('active', 'cancelled', 'expired') DEFAULT 'active' COMMENT 'Estado de la suscripción',
    start_date DATE NOT NULL COMMENT 'Fecha de inicio',
    end_date DATE NOT NULL COMMENT 'Fecha de fin',
    price DECIMAL(10,2) NOT NULL COMMENT 'Precio pagado',
    auto_renew BOOLEAN DEFAULT TRUE COMMENT 'Renovación automática',
    last_daily_bonus DATE DEFAULT NULL COMMENT 'Última fecha de bonus diario',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_end_date (end_date),
    INDEX idx_user_active (user_id, status, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Suscripciones premium';

-- 5. INICIALIZAR PUNTOS PARA USUARIOS EXISTENTES
-- =====================================================
INSERT IGNORE INTO user_points (user_id, points, total_purchased, total_spent)
SELECT id, 0, 0, 0 
FROM users
WHERE id NOT IN (SELECT user_id FROM user_points);

-- 6. FUNCIÓN PARA CALCULAR MINUTOS DISPONIBLES
-- =====================================================
DELIMITER $$

DROP FUNCTION IF EXISTS calculate_available_minutes$$

CREATE FUNCTION calculate_available_minutes(user_points INT)
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE minutes INT DEFAULT 0;
    DECLARE remaining INT;
    
    IF user_points >= 400 THEN
        IF user_points < 800 THEN
            SET minutes = 30;
        ELSEIF user_points < 1600 THEN
            SET minutes = FLOOR(user_points / 800 * 60);
        ELSE
            -- 2 horas base (1600 puntos)
            SET minutes = 120;
            SET remaining = user_points - 1600;
            -- Cada 1000 puntos = 60 minutos adicionales
            SET minutes = minutes + FLOOR(remaining / 1000 * 60);
        END IF;
    END IF;
    
    RETURN minutes;
END$$

DELIMITER ;

-- 7. PROCEDIMIENTO PARA CADUCAR SUSCRIPCIONES
-- =====================================================
DELIMITER $$

DROP PROCEDURE IF EXISTS expire_premium_subscriptions$$

CREATE PROCEDURE expire_premium_subscriptions()
BEGIN
    -- Actualizar suscripciones expiradas
    UPDATE premium_subscriptions 
    SET status = 'expired' 
    WHERE status = 'active' 
    AND end_date < CURDATE();
    
    -- Actualizar usuarios
    UPDATE users u
    LEFT JOIN premium_subscriptions ps ON u.id = ps.user_id AND ps.status = 'active'
    SET u.is_premium = FALSE
    WHERE ps.id IS NULL AND u.is_premium = TRUE;
    
    SELECT ROW_COUNT() as expired_count;
END$$

DELIMITER ;

-- 8. PROCEDIMIENTO PARA DAR BONUS DIARIO A PREMIUM
-- =====================================================
DELIMITER $$

DROP PROCEDURE IF EXISTS give_daily_premium_bonus$$

CREATE PROCEDURE give_daily_premium_bonus()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_user_id INT;
    DECLARE v_sub_id INT;
    DECLARE bonus_points INT DEFAULT 200;  -- 15 minutos = 200 puntos
    
    DECLARE premium_cursor CURSOR FOR
        SELECT ps.id, ps.user_id
        FROM premium_subscriptions ps
        WHERE ps.status = 'active'
        AND ps.end_date >= CURDATE()
        AND (ps.last_daily_bonus IS NULL OR ps.last_daily_bonus < CURDATE());
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN premium_cursor;
    
    read_loop: LOOP
        FETCH premium_cursor INTO v_sub_id, v_user_id;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Añadir puntos
        INSERT INTO user_points (user_id, points)
        VALUES (v_user_id, bonus_points)
        ON DUPLICATE KEY UPDATE points = points + bonus_points;
        
        -- Registrar transacción
        INSERT INTO point_transactions (user_id, type, points, description)
        VALUES (v_user_id, 'premium_daily', bonus_points, 'Bonus diari Premium - 15 minuts gratuïts');
        
        -- Actualizar última fecha de bonus
        UPDATE premium_subscriptions
        SET last_daily_bonus = CURDATE()
        WHERE id = v_sub_id;
        
    END LOOP;
    
    CLOSE premium_cursor;
    
    SELECT COUNT(*) as bonuses_given FROM premium_subscriptions
    WHERE last_daily_bonus = CURDATE();
END$$

DELIMITER ;

-- 9. EVENTO PARA EJECUTAR TAREAS DIARIAS
-- =====================================================
SET GLOBAL event_scheduler = ON;

DROP EVENT IF EXISTS daily_premium_tasks;

CREATE EVENT daily_premium_tasks
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_DATE + INTERVAL 1 DAY + INTERVAL 2 HOUR  -- 2 AM
DO
BEGIN
    CALL expire_premium_subscriptions();
    CALL give_daily_premium_bonus();
END;

-- 10. VISTA PARA INFORMACIÓN COMPLETA DE USUARIO
-- =====================================================
CREATE OR REPLACE VIEW user_full_info AS
SELECT 
    u.id,
    u.username,
    u.email,
    u.is_premium,
    u.premium_expires_at,
    COALESCE(up.points, 0) as points,
    COALESCE(up.total_purchased, 0) as total_purchased,
    COALESCE(up.total_spent, 0) as total_spent,
    calculate_available_minutes(COALESCE(up.points, 0)) as minutes_available,
    ps.type as subscription_type,
    ps.status as subscription_status,
    ps.end_date as subscription_end_date,
    ps.auto_renew as subscription_auto_renew
FROM users u
LEFT JOIN user_points up ON u.id = up.user_id
LEFT JOIN premium_subscriptions ps ON u.id = ps.user_id AND ps.status = 'active';

-- Reactivar verificación de foreign keys
SET FOREIGN_KEY_CHECKS = 1;

-- 11. VERIFICACIÓN FINAL
-- =====================================================
SELECT 
    'Instalación completada correctamente' as status,
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM user_points) as users_with_points,
    (SELECT COUNT(*) FROM point_transactions) as total_transactions,
    (SELECT COUNT(*) FROM premium_subscriptions) as total_subscriptions,
    (SELECT COUNT(*) FROM premium_subscriptions WHERE status = 'active') as active_subscriptions;

-- Mostrar estructura de tablas
SHOW COLUMNS FROM users WHERE Field IN ('is_premium', 'premium_expires_at');
SHOW TABLES LIKE '%point%';
SHOW TABLES LIKE '%premium%';
