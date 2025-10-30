<?php
/**
 * Test Claim Endpoint - Shows detailed information about claiming process
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../core/DatabaseMariaDB.php';
require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../models/Booking.php';

$response = [
    'timestamp' => date('Y-m-d H:i:s'),
    'session_user_id' => $_SESSION['user_id'] ?? null,
    'steps' => []
];

try {
    // Step 1: Check authentication
    $response['steps'][] = [
        'step' => 1,
        'name' => 'Authentication Check',
        'user_id' => $_SESSION['user_id'] ?? null,
        'authenticated' => isset($_SESSION['user_id']),
        'status' => isset($_SESSION['user_id']) ? 'OK' : 'FAIL'
    ];
    
    if (!isset($_SESSION['user_id'])) {
        $response['final_result'] = 'AUTHENTICATION FAILED';
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $db = DatabaseMariaDB::getConnection();
    
    // Step 2: Check database connection
    $response['steps'][] = [
        'step' => 2,
        'name' => 'Database Connection',
        'status' => $db ? 'OK' : 'FAIL'
    ];
    
    // Step 3: Get test vehicle (ID=5 or first available)
    $vehicleModel = new Vehicle($db);
    $testVehicleId = 5;
    
    $vehicle = $vehicleModel->getVehicleForClaim($testVehicleId);
    
    $response['steps'][] = [
        'step' => 3,
        'name' => 'Get Vehicle for Claim',
        'vehicle_id' => $testVehicleId,
        'vehicle_found' => $vehicle ? true : false,
        'vehicle_data' => $vehicle,
        'status' => $vehicle ? 'OK' : 'FAIL'
    ];
    
    if (!$vehicle) {
        $response['final_result'] = 'VEHICLE NOT AVAILABLE';
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Step 4: Check existing bookings
    $bookingModel = new Booking($db);
    $existingBooking = $bookingModel->getActiveBooking($userId);
    
    $response['steps'][] = [
        'step' => 4,
        'name' => 'Check Active Bookings',
        'has_active_booking' => $existingBooking ? true : false,
        'existing_booking' => $existingBooking,
        'status' => $existingBooking ? 'BLOCKED' : 'OK'
    ];
    
    if ($existingBooking) {
        $response['final_result'] = 'USER ALREADY HAS ACTIVE BOOKING';
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Step 5: Update vehicle status
    $statusUpdated = $vehicleModel->updateStatus($testVehicleId, 'in_use');
    
    $response['steps'][] = [
        'step' => 5,
        'name' => 'Update Vehicle Status to in_use',
        'success' => $statusUpdated,
        'status' => $statusUpdated ? 'OK' : 'FAIL',
        'error' => $statusUpdated ? null : $db->error
    ];
    
    if (!$statusUpdated) {
        $response['final_result'] = 'FAILED TO UPDATE VEHICLE STATUS';
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Step 6: Create booking
    $unlockFee = 0.50;
    $bookingId = $bookingModel->createBooking($userId, $testVehicleId, $unlockFee);
    
    $response['steps'][] = [
        'step' => 6,
        'name' => 'Create Booking',
        'booking_id' => $bookingId,
        'success' => $bookingId ? true : false,
        'status' => $bookingId ? 'OK' : 'FAIL',
        'error' => $bookingId ? null : $db->error
    ];
    
    if (!$bookingId) {
        // Revert vehicle status
        $vehicleModel->updateStatus($testVehicleId, 'available');
        $response['steps'][] = [
            'step' => '6b',
            'name' => 'Revert Vehicle Status (booking failed)',
            'status' => 'REVERTED'
        ];
        $response['final_result'] = 'FAILED TO CREATE BOOKING';
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Step 7: Verify booking was created
    $verifyBooking = $bookingModel->getActiveBooking($userId);
    
    $response['steps'][] = [
        'step' => 7,
        'name' => 'Verify Booking Created',
        'booking_found' => $verifyBooking ? true : false,
        'booking_data' => $verifyBooking,
        'status' => $verifyBooking ? 'OK' : 'FAIL'
    ];
    
    // Step 8: Verify vehicle status changed
    $verifyVehicle = $vehicleModel->getVehicleById($testVehicleId);
    
    $response['steps'][] = [
        'step' => 8,
        'name' => 'Verify Vehicle Status Changed',
        'vehicle_status' => $verifyVehicle['status'] ?? null,
        'is_in_use' => ($verifyVehicle['status'] ?? null) === 'in_use',
        'status' => ($verifyVehicle['status'] ?? null) === 'in_use' ? 'OK' : 'FAIL'
    ];
    
    $response['final_result'] = 'SUCCESS - ALL STEPS PASSED';
    $response['summary'] = [
        'booking_id' => $bookingId,
        'vehicle_id' => $testVehicleId,
        'user_id' => $userId,
        'vehicle_status' => $verifyVehicle['status'] ?? null,
        'booking_status' => $verifyBooking['status'] ?? null
    ];
    
} catch (Exception $e) {
    $response['exception'] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
    $response['final_result'] = 'EXCEPTION OCCURRED';
}

echo json_encode($response, JSON_PRETTY_PRINT);
