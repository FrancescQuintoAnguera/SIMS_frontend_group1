-- Script para actualizar la columna last_daily_bonus y el tipo de transacciones
-- Ejecutar este script si ya tienes las tablas creadas

-- 1. Agregar columna last_daily_bonus si no existe
SET @dbname = DATABASE();
SET @tablename = 'users';
SET @columnname = 'last_daily_bonus';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column already exists' AS _message;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " DATE DEFAULT NULL;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. Modificar la columna type de point_transactions para incluir 'premium_bonus'
-- Primero verificamos si la tabla existe
SET @tablename = 'point_transactions';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
  ) > 0,
  CONCAT("ALTER TABLE ", @tablename, " MODIFY COLUMN type ENUM('purchase', 'spend', 'bonus', 'refund', 'premium_daily', 'premium_bonus') NOT NULL;"),
  "SELECT 'Table does not exist yet' AS _message;"
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- 3. Verificar cambios
SELECT 
    'Columna last_daily_bonus agregada a users' AS status
UNION ALL
SELECT 
    'Tipo premium_bonus agregado a point_transactions' AS status;

-- 4. Mostrar estructura actualizada
SHOW COLUMNS FROM users LIKE 'last_daily_bonus';
SHOW COLUMNS FROM point_transactions LIKE 'type';
