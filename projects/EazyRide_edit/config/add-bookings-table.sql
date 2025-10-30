-- Add bookings/reservations table to the database
-- This script should be run after the initial mariadb-init.sql

USE simsdb;

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'confirmed',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_datetime (start_datetime, end_datetime),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add additional fields to vehicles table if they don't exist
ALTER TABLE vehicles 
ADD COLUMN IF NOT EXISTS battery_level INT DEFAULT 100,
ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,8) DEFAULT 41.3851,
ADD COLUMN IF NOT EXISTS longitude DECIMAL(11,8) DEFAULT 2.1734,
ADD COLUMN IF NOT EXISTS status ENUM('available', 'in_use', 'maintenance', 'reserved') DEFAULT 'available',
ADD COLUMN IF NOT EXISTS vehicle_type VARCHAR(50) DEFAULT 'electric',
ADD COLUMN IF NOT EXISTS is_accessible BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS accessibility_features JSON,
ADD COLUMN IF NOT EXISTS price_per_minute DECIMAL(5,2) DEFAULT 0.30,
ADD COLUMN IF NOT EXISTS image_url VARCHAR(255);

SELECT 'Bookings table created successfully!' AS message;
