-- Agregar columnas necesarias a vehicles
USE simsdb;

ALTER TABLE vehicles 
  ADD COLUMN IF NOT EXISTS status ENUM('available', 'in_use', 'charging', 'maintenance', 'reserved') DEFAULT 'available',
  ADD COLUMN IF NOT EXISTS battery_level INT DEFAULT 100,
  ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,8) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS longitude DECIMAL(11,8) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS vehicle_type ENUM('car', 'bike', 'scooter', 'motorcycle') DEFAULT 'car',
  ADD COLUMN IF NOT EXISTS is_accessible BOOLEAN DEFAULT FALSE,
  ADD COLUMN IF NOT EXISTS accessibility_features JSON DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS price_per_minute DECIMAL(5,2) DEFAULT 0.35,
  ADD COLUMN IF NOT EXISTS image_url VARCHAR(255) DEFAULT NULL;

-- Actualizar ubicaciones a Amposta
UPDATE vehicles SET 
    latitude = 40.7117 + (RAND() - 0.5) * 0.01,
    longitude = 0.5783 + (RAND() - 0.5) * 0.01,
    battery_level = FLOOR(60 + RAND() * 40),
    status = 'available'
WHERE latitude IS NULL;

SELECT 'Setup completed' as message;
