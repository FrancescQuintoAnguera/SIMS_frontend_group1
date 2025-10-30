-- Verificar y agregar columna is_premium si no existe
-- También agrega premium_expires_at para suscripciones

USE eazyride;

-- Agregar is_premium si no existe
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'eazyride' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'is_premium');

SET @sqlstmt := IF(@exist = 0, 
'ALTER TABLE users ADD COLUMN is_premium TINYINT(1) DEFAULT 0 AFTER email',
'SELECT "Column is_premium already exists" AS message');

PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar premium_expires_at si no existe
SET @exist2 := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'eazyride' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'premium_expires_at');

SET @sqlstmt2 := IF(@exist2 = 0,
'ALTER TABLE users ADD COLUMN premium_expires_at DATETIME NULL AFTER is_premium',
'SELECT "Column premium_expires_at already exists" AS message');

PREPARE stmt2 FROM @sqlstmt2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Agregar premium_type si no existe (monthly/yearly)
SET @exist3 := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'eazyride' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'premium_type');

SET @sqlstmt3 := IF(@exist3 = 0,
'ALTER TABLE users ADD COLUMN premium_type VARCHAR(20) NULL AFTER premium_expires_at',
'SELECT "Column premium_type already exists" AS message');

PREPARE stmt3 FROM @sqlstmt3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

-- Mostrar estructura actual
DESCRIBE users;

SELECT 'Premium columns check complete!' AS status;
