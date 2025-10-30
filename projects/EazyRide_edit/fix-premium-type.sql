-- Corregir tabla premium_subscriptions para evitar errores de tipo
-- Este script se asegura de que la columna type tenga el tamaño correcto

-- Recrear la tabla si es necesario
DROP TABLE IF EXISTS premium_subscriptions;

CREATE TABLE premium_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'monthly',
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    auto_renew BOOLEAN DEFAULT TRUE,
    last_daily_bonus DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_end_date (end_date),
    CHECK (type IN ('monthly', 'annual')),
    CHECK (status IN ('active', 'cancelled', 'expired'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Asegurar que la columna last_daily_bonus existe en users
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_daily_bonus DATE DEFAULT NULL;

SELECT 'Tabla premium_subscriptions corregida' as mensaje;
