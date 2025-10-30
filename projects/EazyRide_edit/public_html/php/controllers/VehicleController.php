<?php

/**
 * VehicleController
 * Handles vehicle-related business logic
 */

require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../models/Booking.php';

class VehicleController {
    private $vehicleModel;
    private $bookingModel;
    
    public function __construct($dbConnection) {
        $this->vehicleModel = new Vehicle($dbConnection);
        $this->bookingModel = new Booking($dbConnection);
    }
    
    /**
     * Claim a vehicle
     */
    public function claimVehicle($vehicleId, $userId) {
        error_log("=== VehicleController::claimVehicle START ===");
        error_log("Vehicle ID: $vehicleId");
        error_log("User ID: $userId");
        
        // Verificar que el vehículo existe y está disponible
        $vehicle = $this->vehicleModel->getVehicleForClaim($vehicleId);
        
        if (!$vehicle) {
            error_log("ERROR: Vehicle not available or not found");
            return [
                'success' => false,
                'message' => 'Vehicle not available',
                'code' => 404
            ];
        }
        
        error_log("Vehicle found: " . json_encode($vehicle));
        
        // Verificar que el usuario no tiene otro vehículo activo
        $activeBooking = $this->bookingModel->getActiveBooking($userId);
        if ($activeBooking) {
            error_log("ERROR: User already has active booking: " . json_encode($activeBooking));
            return [
                'success' => false,
                'message' => 'You already have an active booking',
                'code' => 400
            ];
        }
        
        error_log("No active bookings for user");
        
        // Actualizar estado del vehículo
        error_log("Updating vehicle status to 'in_use'...");
        if (!$this->vehicleModel->updateStatus($vehicleId, 'in_use')) {
            error_log("ERROR: Failed to update vehicle status");
            return [
                'success' => false,
                'message' => 'Failed to update vehicle status',
                'code' => 500
            ];
        }
        
        error_log("Vehicle status updated successfully");
        
        // Crear booking
        error_log("Creating booking...");
        $unlockFee = 0.50;
        $bookingId = $this->bookingModel->createBooking($userId, $vehicleId, $unlockFee);
        
        if (!$bookingId) {
            error_log("ERROR: Failed to create booking");
            // Revertir cambio de estado si falla el booking
            $this->vehicleModel->updateStatus($vehicleId, 'available');
            return [
                'success' => false,
                'message' => 'Failed to create booking',
                'code' => 500
            ];
        }
        
        error_log("SUCCESS: Booking created with ID: $bookingId");
        
        // Formatear ubicación
        if (isset($vehicle['latitude']) && isset($vehicle['longitude'])) {
            $vehicle['location'] = [
                'lat' => (float)$vehicle['latitude'],
                'lng' => (float)$vehicle['longitude']
            ];
        } else {
            $vehicle['location'] = [
                'lat' => 40.7117,
                'lng' => 0.5783
            ];
        }
        
        // Asegurar que el status es in_use
        $vehicle['status'] = 'in_use';
        
        // Limpiar campos
        unset($vehicle['latitude']);
        unset($vehicle['longitude']);
        
        error_log("=== SUCCESS: Vehicle claimed successfully ===");
        error_log("Booking ID: $bookingId");
        error_log("Vehicle: " . json_encode($vehicle));
        
        return [
            'success' => true,
            'message' => 'Vehicle claimed successfully',
            'vehicle' => $vehicle,
            'unlock_fee' => $unlockFee,
            'booking_id' => $bookingId,
            'code' => 200
        ];
    }
    
    /**
     * Get current vehicle for user
     */
    public function getCurrentVehicle($userId) {
        // Buscar booking activo
        $booking = $this->bookingModel->getActiveBooking($userId);
        
        if (!$booking) {
            return [
                'success' => false,
                'message' => 'No active vehicle',
                'code' => 404
            ];
        }
        
        // Obtener datos completos del vehículo
        $vehicle = $this->vehicleModel->getVehicleById($booking['vehicle_id']);
        
        if (!$vehicle) {
            return [
                'success' => false,
                'message' => 'Vehicle not found',
                'code' => 404
            ];
        }
        
        // Asegurar que todos los campos necesarios están presentes
        $vehicle['id'] = $vehicle['id'] ?? $booking['vehicle_id'];
        $vehicle['battery'] = $vehicle['battery'] ?? 85;
        $vehicle['status'] = $vehicle['status'] ?? 'in_use';
        
        // Asegurar que location existe
        if (!isset($vehicle['location'])) {
            $vehicle['location'] = [
                'lat' => 40.7117,
                'lng' => 0.5783
            ];
        }
        
        return [
            'success' => true,
            'vehicle' => $vehicle,
            'booking' => [
                'id' => $booking['id'],
                'start_datetime' => $booking['start_datetime'],
                'status' => $booking['status']
            ],
            'code' => 200
        ];
    }
    
    /**
     * Release vehicle
     */
    public function releaseVehicle($userId) {
        // Buscar booking activo
        $booking = $this->bookingModel->getActiveBooking($userId);
        
        if (!$booking) {
            return [
                'success' => false,
                'message' => 'No active vehicle',
                'code' => 404
            ];
        }
        
        $vehicleId = $booking['vehicle_id'];
        
        // Actualizar estado del vehículo
        if (!$this->vehicleModel->updateStatus($vehicleId, 'available')) {
            return [
                'success' => false,
                'message' => 'Failed to update vehicle status',
                'code' => 500
            ];
        }
        
        // Completar booking
        if (!$this->bookingModel->completeBooking($vehicleId, $userId)) {
            return [
                'success' => false,
                'message' => 'Failed to complete booking',
                'code' => 500
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Vehicle released successfully',
            'code' => 200
        ];
    }
    
    /**
     * Get available vehicles
     */
    public function getAvailableVehicles() {
        $vehicles = $this->vehicleModel->getAvailableVehicles();
        
        // Procesar cada vehículo
        foreach ($vehicles as &$vehicle) {
            // Formatear ubicación
            if (isset($vehicle['latitude']) && isset($vehicle['longitude'])) {
                $vehicle['location'] = [
                    'lat' => (float)$vehicle['latitude'],
                    'lng' => (float)$vehicle['longitude']
                ];
            } else {
                $vehicle['location'] = [
                    'lat' => 40.7117 + (rand(-100, 100) / 10000),
                    'lng' => 0.5783 + (rand(-100, 100) / 10000)
                ];
            }
            
            // Asegurar campos necesarios
            $vehicle['status'] = $vehicle['status'] ?? 'available';
            $vehicle['battery'] = $vehicle['battery_level'] ?? rand(60, 100);
            $vehicle['type'] = $vehicle['vehicle_type'] ?? 'car';
            $vehicle['price_per_minute'] = (float)($vehicle['price_per_minute'] ?? 0.35);
            $vehicle['image_url'] = $vehicle['image_url'] ?? '/images/default-car.jpg';
            $vehicle['is_accessible'] = (bool)($vehicle['is_accessible'] ?? false);
            
            // Limpiar campos duplicados
            unset($vehicle['latitude']);
            unset($vehicle['longitude']);
            unset($vehicle['battery_level']);
            unset($vehicle['vehicle_type']);
        }
        
        return [
            'success' => true,
            'vehicles' => $vehicles,
            'code' => 200
        ];
    }
}
