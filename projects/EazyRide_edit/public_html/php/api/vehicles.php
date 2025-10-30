<?php

/**
 * Vehicles API Endpoint
 * Routes requests to VehicleController
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { 
    http_response_code(200); 
    exit(); 
}

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit();
}

// Cargar dependencias
require_once __DIR__ . '/../core/DatabaseMariaDB.php';
require_once __DIR__ . '/../controllers/VehicleController.php';

// Leer el body JSON si es una petición POST con JSON
$jsonInput = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $jsonInput = json_decode(file_get_contents('php://input'), true);
}

// Determinar la acción (prioridad: GET, JSON body, POST form, default)
$action = $_GET['action'] ?? 
          ($jsonInput['action'] ?? null) ?? 
          $_POST['action'] ?? 
          'list';

error_log("=== VEHICLES API REQUEST ===");
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
error_log("Action detected: $action");
error_log("JSON Input: " . json_encode($jsonInput));

try {
    // Conexión a la base de datos
    $db = DatabaseMariaDB::getConnection();
    
    // Instanciar controlador
    $vehicleController = new VehicleController($db);
    
    // Obtener user ID de la sesión
    $userId = $_SESSION['user_id'];
    
    switch ($action) {
        case 'claim':
            // Reclamar un vehículo
            $vehicleId = $jsonInput['vehicle_id'] ?? $_POST['vehicle_id'] ?? null;
            
            error_log("=== CLAIM REQUEST ===");
            error_log("User ID: $userId");
            error_log("Vehicle ID: $vehicleId");
            error_log("JSON Input: " . json_encode($jsonInput));
            
            if (!$vehicleId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Vehicle ID is required'
                ]);
                exit();
            }
            
            $result = $vehicleController->claimVehicle($vehicleId, $userId);
            error_log("Claim result: " . json_encode($result));
            
            // Guardar en sesión si fue exitoso
            if ($result['success']) {
                $_SESSION['current_vehicle_id'] = $vehicleId;
                error_log("Vehicle ID saved to session: $vehicleId");
            } else {
                error_log("Claim failed: " . ($result['message'] ?? 'Unknown error'));
            }
            
            http_response_code($result['code'] ?? 200);
            unset($result['code']);
            echo json_encode($result);
            break;
            
        case 'current':
            // Obtener vehículo actual del usuario
            $result = $vehicleController->getCurrentVehicle($userId);
            
            // Actualizar sesión si hay vehículo activo
            if ($result['success'] && isset($result['vehicle']['id'])) {
                $_SESSION['current_vehicle_id'] = $result['vehicle']['id'];
            }
            
            http_response_code($result['code'] ?? 200);
            unset($result['code']);
            echo json_encode($result);
            break;
            
        case 'release':
            // Liberar vehículo actual
            $result = $vehicleController->releaseVehicle($userId);
            
            // Limpiar sesión si fue exitoso
            if ($result['success']) {
                unset($_SESSION['current_vehicle_id']);
            }
            
            http_response_code($result['code'] ?? 200);
            unset($result['code']);
            echo json_encode($result);
            break;
        
        case 'available':
        case 'list':
        default:
            // Obtener vehículos disponibles
            $result = $vehicleController->getAvailableVehicles();
            
            http_response_code($result['code'] ?? 200);
            unset($result['code']);
            echo json_encode($result);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}