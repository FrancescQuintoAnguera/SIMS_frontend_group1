<?php
/**
 * Configuración de base de datos simplificada
 */

// Cargar .env
$env_locations = [__DIR__ . '/../.env', '/var/www/html/.env'];
foreach ($env_locations as $env_file) {
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos(trim($line), '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                putenv(trim($key) . '=' . trim($value));
            }
        }
        break;
    }
}

// Cargar clases - usar ruta absoluta desde public_html
$base_dir = dirname(__DIR__);  // public_html
require_once $base_dir . '/php/core/DatabaseMariaDB.php';
require_once $base_dir . '/php/core/TenantManager.php';
require_once $base_dir . '/php/core/TenantAwareDatabase.php';

class Database {
    public static function getMariaDBConnection() {
        return DatabaseMariaDB::getConnection();
    }
    
    public static function getTenantAwareDB() {
        static $instance = null;
        if ($instance === null) {
            $instance = new TenantAwareDatabase();
        }
        return $instance;
    }

    public static function getTenantManager() {
        return TenantManager::getInstance();
    }
}

function getDB() {
    return Database::getMariaDBConnection();
}

function getTenantDB() {
    return Database::getTenantAwareDB();
}

function getTenantManager() {
    return Database::getTenantManager();
}
