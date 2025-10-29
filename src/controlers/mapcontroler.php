<?php

// Incluir el modelo
require_once __DIR__ . '/../model/vehiclemodel.php';

class MapController {
    
    private $basePath;
    private $model; // Nueva propiedad para el modelo

    // El constructor inicializa el modelo
    public function __construct($basePath = '') {
        $this->basePath = $basePath;
        $this->model = new VehicleModel(); // Inicializa la conexión a la DB
    }

    // ------------------------------------------------------------------

    public function showPublicMap() {
        // LECTURA DESDE LA BASE DE DATOS
        $chargingPoints = $this->model->getAllVehicles(); 
        $base_path = $this->basePath; 
        require __DIR__ . '/../view/map.php'; 
    }

    public function showAdminCrud() {
        $error = null;
        $success = null;

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
            $action = $_POST['action'] ?? '';
            
            // Los handlers ahora operan directamente con el modelo
            switch ($action) {
                case 'save_station':
                    $success = $this->handleSaveNewPoint($error);
                    break;
                case 'delete_point':
                    $success = $this->handleDeletePoint($error);
                    break;
                case 'update_point':
                    $success = $this->handleUpdatePoint($error);
                    break;
            }
        }
        
        // Carga los puntos actualizados desde la DB para la tabla
        $chargingPoints = $this->model->getAllVehicles();
        $base_path = $this->basePath;
        require __DIR__ . '/../view/admin/create-point.php';
    }

    private function handleSaveNewPoint(&$error) {
        $name = htmlspecialchars($_POST['name'] ?? 'Charging Point');
        $latitude = floatval($_POST['latitude'] ?? 0); 
        $longitude = floatval($_POST['longitude'] ?? 0); 

        if ($latitude == 0 || $longitude == 0) {
            $error = "You must click on the map to set the coordinates!";
            return null;
        }

        // CREAR EN LA BASE DE DATOS
        if ($this->model->createVehicle($name, $latitude, $longitude)) {
            return "Point **'$name'** saved successfully.";
        } else {
            $error = "Error saving point to the database.";
            return null;
        }
    }

    private function handleDeletePoint(&$error) {
        $idToDelete = intval($_POST['point_id'] ?? 0);
        
        // ELIMINAR EN LA BASE DE DATOS
        if ($this->model->deleteVehicle($idToDelete)) {
            return "Point with ID **#$idToDelete** deleted successfully.";
        } else {
            $error = "Error deleting point or point not found.";
            return null;
        }
    }

    private function handleUpdatePoint(&$error) {
        $idToUpdate = intval($_POST['edit_id'] ?? 0);
        $newName = htmlspecialchars($_POST['edit_name'] ?? '');
        $newStatus = htmlspecialchars($_POST['edit_status'] ?? '');
        $newLat = floatval($_POST['edit_latitude'] ?? 0);
        $newLng = floatval($_POST['edit_longitude'] ?? 0);

        if (empty($newName) || $newLat == 0 || $newLng == 0) {
            $error = "All fields (Name, Lat, Lng) must be valid.";
            return null;
        }

        // ACTUALIZAR EN LA BASE DE DATOS
        if ($this->model->updateVehicle($idToUpdate, $newName, $newLat, $newLng, $newStatus)) {
            return "Point **'$newName'** updated successfully.";
        } else {
            $error = "Error updating point in the database.";
            return null;
        }
    }
    
    public function showNotFound() {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404</h1><p>Page Not Found.</p>";
    }
}