-- Crear tabla vehicle_logs si no existe
CREATE TABLE IF NOT EXISTS vehicle_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar algunos logs de ejemplo (opcional)
-- INSERT INTO vehicle_logs (vehicle_id, user_id, action) VALUES 
-- (1, 1, 'lock'),
-- (1, 1, 'unlock'),
-- (2, 1, 'toggle_lights');
