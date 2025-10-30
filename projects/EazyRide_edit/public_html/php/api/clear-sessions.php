<?php
/**
 * Clear all PHP sessions
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Credentials: true');

// Limpiar la sesión actual
session_start();
session_unset();
session_destroy();

// Limpiar el directorio de sesiones (solo para desarrollo)
$sessionPath = session_save_path();
if (empty($sessionPath)) {
    $sessionPath = '/tmp';
}

$cleaned = 0;
if (is_dir($sessionPath)) {
    $files = glob($sessionPath . '/sess_*');
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file) && unlink($file)) {
                $cleaned++;
            }
        }
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Sessions cleared',
    'sessions_deleted' => $cleaned,
    'session_path' => $sessionPath
]);
?>
