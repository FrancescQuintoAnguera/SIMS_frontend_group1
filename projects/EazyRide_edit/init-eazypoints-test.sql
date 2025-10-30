-- Script para inicializar sistema EazyPoints con datos de prueba
-- Ejecutar DESPUÉS de crear las tablas con eazypoints-schema.sql

-- Agregar 1000 puntos de prueba al usuario con ID 1
-- (Cambia el ID según tu usuario de prueba)
INSERT INTO user_points (user_id, points, total_purchased)
VALUES (1, 1000, 1000)
ON DUPLICATE KEY UPDATE 
    points = 1000,
    total_purchased = 1000;

-- Registrar transacción de prueba
INSERT INTO point_transactions 
(user_id, type, points, price, package_name, discount, description)
VALUES 
(1, 'purchase', 1000, 0.00, 'Test', 0, 'Punts de prova inicials');

-- Ver el resultado
SELECT 
    u.id,
    u.username,
    up.points,
    up.total_purchased,
    up.total_spent
FROM users u
LEFT JOIN user_points up ON u.id = up.user_id
WHERE u.id = 1;
