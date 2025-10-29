<?php

class VehicleModel {
    private $db;

    public function __construct() {
        // 🚨 CONFIGURACIÓN DE CONEXIÓN A POSTGRESQL 🚨
        $host = 'localhost';
        $dbname = 'fleetly'; // Reemplaza con el nombre de tu DB
        $user = 'moreno'; // Reemplaza con tu usuario
        $password = 'moreno'; // Reemplaza con tu contraseña
        $port = '5050'; 

        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            $this->db = new PDO($dsn, $user, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // En un entorno de producción, esto debería ir a un log, no al usuario.
            die("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Recupera todos los vehículos (puntos de carga) con sus coordenadas y estado.
     * @return array
     */
    public function getAllVehicles() {
        $stmt = $this->db->prepare("SELECT id, name, latitude, longitude, status_car AS status FROM \"Vehicles\" WHERE deleted_at IS NULL ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Añade un nuevo vehículo (punto de carga).
     * @return bool
     */
    public function createVehicle($name, $latitude, $longitude) {
        $sql = "INSERT INTO \"Vehicles\" (name, latitude, longitude, id_tenan, id_type_vehicle, status_car, vehicle_code) 
                VALUES (:name, :latitude, :longitude, 1, 1, 'available', :code)";
        
        // Generación simple de un código temporal para cumplir con NOT NULL
        $vehicle_code = 'V' . time(); 
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'name' => $name,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'code' => $vehicle_code
        ]);
    }

    /**
     * Actualiza un vehículo existente.
     * @return bool
     */
    public function updateVehicle($id, $name, $latitude, $longitude, $status) {
        $sql = "UPDATE \"Vehicles\" SET 
                name = :name, 
                latitude = :latitude, 
                longitude = :longitude, 
                status_car = :status,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $name,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'status' => $status
        ]);
    }

    /**
     * Elimina un vehículo (Soft Delete).
     * @return bool
     */
    public function deleteVehicle($id) {
        $sql = "UPDATE \"Vehicles\" SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}