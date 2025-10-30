-- Script para insertar vehículos de prueba
-- Ejecutar solo si la tabla vehicles está vacía

-- Verificar si hay vehículos
SELECT COUNT(*) as total_vehicles FROM vehicles;

-- Si no hay vehículos, insertar algunos de prueba
INSERT INTO vehicles (plate, brand, model, year) VALUES
('ABC1234', 'Tesla', 'Model 3', 2023),
('DEF5678', 'Tesla', 'Model Y', 2022),
('GHI9012', 'Nissan', 'Leaf', 2021),
('JKL3456', 'BMW', 'i3', 2022),
('MNO7890', 'Renault', 'Zoe', 2023),
('PQR1234', 'Volkswagen', 'ID.3', 2022),
('STU5678', 'Hyundai', 'Kona Electric', 2021),
('VWX9012', 'Peugeot', 'e-208', 2023),
('YZA3456', 'Fiat', '500e', 2022),
('BCD7890', 'Mini', 'Cooper SE', 2023)
ON DUPLICATE KEY UPDATE brand=brand;

-- Verificar los vehículos insertados
SELECT * FROM vehicles;
