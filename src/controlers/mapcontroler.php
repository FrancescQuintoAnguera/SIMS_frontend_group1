<?php

class MapController {

    public function __construct() {
    }

    private function getJsonFilePath() {
        // La carpeta 'data' está al lado de 'controlers'
        return __DIR__ . '/../data/charging_points.json';
    }

    private function readPoints() {
        $jsonFile = $this->getJsonFilePath();
        if (file_exists($jsonFile)) {
            $jsonData = file_get_contents($jsonFile);
            return json_decode($jsonData, true) ?: []; 
        }
        return [];
    }

    private function writePoints(array $points) {
        $jsonFile = $this->getJsonFilePath();
        // Asegúrate de que PHP tiene permisos de escritura en la carpeta 'data/'
        return file_put_contents($jsonFile, json_encode($points, JSON_PRETTY_PRINT)) !== false; 
    }

    // ------------------------------------------------------------------

    public function showPublicMap() {
        $chargingPoints = $this->readPoints();
        // Carga la vista desde view/map.php
        require __DIR__ . '/../view/map.php'; 
    }

    public function showAdminCrud() {
        $error = null;
        $success = null;

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
            $points = $this->readPoints();
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'save_station':
                    $success = $this->handleSaveNewPoint($points, $error);
                    break;
                case 'delete_point':
                    $success = $this->handleDeletePoint($points, $error);
                    break;
                case 'update_point':
                    $success = $this->handleUpdatePoint($points, $error);
                    break;
            }
        }
        
        $chargingPoints = $this->readPoints();
        // Carga la vista desde view/admin/create-point.php
        require __DIR__ . '/../view/admin/create-point.php';
    }

    private function handleSaveNewPoint(array &$points, &$error) {
        $name = htmlspecialchars($_POST['name'] ?? 'Charging Point');
        $latitude = htmlspecialchars($_POST['latitude'] ?? null); 
        $longitude = htmlspecialchars($_POST['longitude'] ?? null); 

        if (empty($latitude) || empty($longitude)) {
            $error = "You must click on the map to set the coordinates!";
            return null;
        }

        $lastId = end($points)['id'] ?? 100;
        $newId = $lastId + 1;
        
        $newPoint = [
            'id' => $newId,
            'name' => $name,
            'latitude' => floatval($latitude), 
            'longitude' => floatval($longitude),
            'status' => 'inactive' 
        ];

        $points[] = $newPoint;
        
        if ($this->writePoints($points)) {
            return "Point **'$name'** saved successfully.";
        } else {
            $error = "Error writing to the data file. Check permissions on the 'data/' folder.";
            return null;
        }
    }

    private function handleDeletePoint(array &$points, &$error) {
        $idToDelete = intval($_POST['point_id'] ?? 0);
        
        $initialCount = count($points);
        $points = array_filter($points, function($point) use ($idToDelete) {
            return $point['id'] !== $idToDelete;
        });

        if (count($points) < $initialCount && $this->writePoints($points)) {
            return "Point with ID **#$idToDelete** deleted successfully.";
        } else {
            $error = "Error deleting point or point not found.";
            return null;
        }
    }

    private function handleUpdatePoint(array &$points, &$error) {
        $idToUpdate = intval($_POST['edit_id'] ?? 0);
        $newName = htmlspecialchars($_POST['edit_name'] ?? '');
        $newStatus = htmlspecialchars($_POST['edit_status'] ?? '');
        $newLat = floatval($_POST['edit_latitude'] ?? 0);
        $newLng = floatval($_POST['edit_longitude'] ?? 0);

        if (empty($newName) || $newLat == 0 || $newLng == 0) {
            $error = "All fields (Name, Lat, Lng) must be valid.";
            return null;
        }

        foreach ($points as $key => $point) {
            if ($point['id'] === $idToUpdate) {
                $points[$key]['name'] = $newName;
                $points[$key]['status'] = $newStatus;
                $points[$key]['latitude'] = $newLat;
                $points[$key]['longitude'] = $newLng;

                if ($this->writePoints($points)) {
                    return "Point **'$newName'** updated successfully.";
                } else {
                    $error = "Error writing to the data file.";
                    return null;
                }
            }
        }
        $error = "Point not found for update.";
        return null;
    }
    
    public function showNotFound() {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404</h1><p>Page Not Found.</p>";
    }
}