<?php
/**
 * Configuración global de errores para APIs
 * Incluir este archivo al inicio de todos los endpoints API
 */

// Desactivar la visualización de errores en producción
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

// Habilitar el registro de errores
ini_set('log_errors', '1');

// Configurar nivel de reporte de errores
// En producción, solo reportar errores críticos
error_reporting(E_ERROR | E_PARSE);

// En desarrollo, descomentar la siguiente línea para ver todos los errores
// error_reporting(E_ALL);

// Configurar archivo de log de errores
$log_path = __DIR__ . '/../../logs';
if (!file_exists($log_path)) {
    @mkdir($log_path, 0755, true);
}
ini_set('error_log', $log_path . '/php_errors.log');

// Manejador de errores personalizado para APIs
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // No interrumpir la ejecución para warnings
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    // Registrar el error
    error_log("Error [$errno]: $errstr in $errfile on line $errline");
    
    // Para errores fatales, devolver JSON
    if ($errno === E_ERROR || $errno === E_PARSE || $errno === E_CORE_ERROR) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error intern del servidor',
            'error_code' => $errno
        ]);
        exit;
    }
    
    return true;
});

// Manejador de excepciones no capturadas
set_exception_handler(function($exception) {
    error_log("Uncaught exception: " . $exception->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error intern del servidor',
        'error' => $exception->getMessage()
    ]);
    exit;
});

// Función auxiliar para limpiar el buffer de salida si hay contenido no deseado
function clean_output_buffer() {
    while (ob_get_level()) {
        ob_end_clean();
    }
}

// Iniciar buffer de salida para capturar cualquier output no deseado
ob_start();

// Registrar función de limpieza para ejecutar antes de enviar la respuesta
register_shutdown_function(function() {
    $output = ob_get_clean();
    
    // Si hay output no deseado (HTML, warnings, etc.), limpiar
    if (!empty($output) && strpos($output, '{') !== 0) {
        // Buscar JSON válido en el output
        $json_start = strpos($output, '{');
        if ($json_start !== false) {
            $output = substr($output, $json_start);
        } else {
            // No hay JSON, devolver error
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error de formato en la respuesta del servidor'
            ]);
            return;
        }
    }
    
    echo $output;
});
?>
