<?php
class DatabaseMariaDB {
    private static $conn;
    private static $env_loaded = false;

    private static function loadEnv() {
        if (self::$env_loaded) {
            return;
        }

        // Buscar archivo .env en múltiples ubicaciones
        $possible_locations = [
            __DIR__ . '/../../../.env',  // Desde public_html/php/core/
            __DIR__ . '/../../.env',      // Para web server (public_html/)
            '/var/www/html/.env',         // Docker
        ];

        foreach ($possible_locations as $env_file) {
            if (file_exists($env_file)) {
                $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos($line, '=') !== false && strpos(trim($line), '#') !== 0) {
                        list($key, $value) = explode('=', $line, 2);
                        $key = trim($key);
                        $value = trim($value);
                        if (!getenv($key)) {
                            putenv("$key=$value");
                        }
                    }
                }
                self::$env_loaded = true;
                return;
            }
        }
    }

    public static function getConnection() {
        if (!self::$conn) {
            self::loadEnv();

            $host = getenv('DB_HOST') ?: 'localhost';
            $user = getenv('DB_USER');
            $pass = getenv('DB_PASS');
            $dbname = getenv('DB_NAME') ?: 'simsdb';

            // Validar que las credenciales críticas existen
            if (!$user || !$pass) {
                throw new Exception("Database credentials not configured. Please check your .env file.");
            }

            // Ajustar host si es nombre de contenedor Docker
            if (file_exists('/.dockerenv')) {
                // Estamos en Docker - usar nombre del contenedor
                if ($host === 'localhost') {
                    $host = 'mariadb';
                }
            } else {
                // Estamos en el host - usar IP
                if ($host === 'mariadb' || $host === 'localhost') {
                    $host = '127.0.0.1';
                }
            }

            try {
                $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
                self::$conn = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } catch (PDOException $e) {
                throw new Exception("MariaDB connection failed: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}