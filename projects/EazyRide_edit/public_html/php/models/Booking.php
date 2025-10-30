<?php

/**
 * Booking Model
 * Handles booking/reservation operations
 */

class Booking {
    private $db;
    
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }
    
    /**
     * Create a new booking
     */
    public function createBooking($userId, $vehicleId, $unlockFee = 0.50) {
        $startTime = date('Y-m-d H:i:s');
        $endTime = date('Y-m-d H:i:s', strtotime('+2 hours')); // Estimado 2 horas
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO bookings (user_id, vehicle_id, start_datetime, end_datetime, total_cost, status)
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            
            if ($stmt->execute([$userId, $vehicleId, $startTime, $endTime, $unlockFee])) {
                $bookingId = $this->db->lastInsertId();
                error_log("Booking created successfully: ID=$bookingId, User=$userId, Vehicle=$vehicleId");
                return $bookingId;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Booking Model Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get active booking for user
     */
    public function getActiveBooking($userId) {
        $stmt = $this->db->prepare("
            SELECT b.*, v.plate as license_plate, v.model, v.brand
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            WHERE b.user_id = ? AND b.status = 'active'
            ORDER BY b.start_datetime DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        return $booking !== false ? $booking : null;
    }
    
    /**
     * Get active booking by vehicle
     */
    public function getActiveBookingByVehicle($vehicleId) {
        $stmt = $this->db->prepare("
            SELECT * FROM bookings 
            WHERE vehicle_id = ? AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$vehicleId]);
        
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        return $booking !== false ? $booking : null;
    }
    
    /**
     * Complete a booking
     */
    public function completeBooking($vehicleId, $userId) {
        $stmt = $this->db->prepare("
            UPDATE bookings 
            SET end_datetime = NOW(), 
                status = 'completed'
            WHERE vehicle_id = ? AND user_id = ? AND status = 'active'
        ");
        return $stmt->execute([$vehicleId, $userId]);
    }
    
    /**
     * Cancel a booking
     */
    public function cancelBooking($bookingId) {
        $stmt = $this->db->prepare("
            UPDATE bookings 
            SET status = 'cancelled'
            WHERE id = ?
        ");
        return $stmt->execute([$bookingId]);
    }
    
    /**
     * Get booking history for user
     */
    public function getBookingHistory($userId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT b.*, v.plate as license_plate, v.model, v.brand
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            WHERE b.user_id = ?
            ORDER BY b.start_datetime DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
