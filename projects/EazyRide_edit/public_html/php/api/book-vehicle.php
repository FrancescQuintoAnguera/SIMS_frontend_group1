<?php
/**
 * Book Vehicle API Endpoint
 * Handles vehicle booking requests with validation and conflict checking
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

session_start();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required',
        'message_ca' => 'Autenticació requerida',
        'message_es' => 'Autenticación requerida',
        'message_en' => 'Authentication required'
    ]);
    exit();
}

require_once __DIR__ . '/../core/DatabaseMariaDB.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $vehicle_id = isset($input['vehicle_id']) ? intval($input['vehicle_id']) : 0;
    $start_datetime = $input['start_datetime'] ?? '';
    $end_datetime = $input['end_datetime'] ?? '';
    $user_id = intval($_SESSION['user_id']);
    
    // Validation: Check required fields
    if ($vehicle_id == 0 || empty($start_datetime) || empty($end_datetime)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields',
            'message_ca' => 'Falten camps obligatoris',
            'message_es' => 'Faltan campos obligatorios',
            'message_en' => 'Missing required fields'
        ]);
        exit();
    }
    
    $db = DatabaseMariaDB::getConnection();
    
    // ====================================================================
    // NUEVA VALIDACIÓN: Verificar que el usuario NO tenga ya una reserva activa
    // ====================================================================
    $stmt = $db->prepare("
        SELECT b.id, v.model 
        FROM bookings b
        INNER JOIN vehicles v ON b.vehicle_id = v.id
        WHERE b.user_id = ? 
        AND b.status IN ('confirmed', 'active')
        AND b.end_datetime >= NOW()
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $existingBooking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingBooking) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'You already have an active booking',
            'message_ca' => 'Ja tens un vehicle reservat. Finalitza la reserva actual abans de fer-ne una altra.',
            'message_es' => 'Ya tienes un vehículo reservado. Finaliza la reserva actual antes de hacer otra.',
            'message_en' => 'You already have an active booking. Please finish your current booking first.',
            'existing_vehicle' => $existingBooking['model']
        ]);
        exit();
    }
    
    // Validation: Check date/time format
    $start_dt = DateTime::createFromFormat('Y-m-d H:i:s', $start_datetime);
    $end_dt = DateTime::createFromFormat('Y-m-d H:i:s', $end_datetime);
    
    if (!$start_dt || !$end_dt) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid date/time format. Use YYYY-MM-DD HH:MM:SS',
            'message_ca' => 'Format de data/hora invàlid. Utilitzeu AAAA-MM-DD HH:MM:SS',
            'message_es' => 'Formato de fecha/hora inválido. Use AAAA-MM-DD HH:MM:SS',
            'message_en' => 'Invalid date/time format. Use YYYY-MM-DD HH:MM:SS'
        ]);
        exit();
    }
    
    // Validation: End must be after start
    if ($end_dt <= $start_dt) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'End date/time must be after start date/time',
            'message_ca' => 'La data/hora de finalització ha de ser posterior a la d\'inici',
            'message_es' => 'La fecha/hora de finalización debe ser posterior a la de inicio',
            'message_en' => 'End date/time must be after start date/time'
        ]);
        exit();
    }
    
    // Validation: Dates must be in the future (permitir hasta 1 minuto en el pasado por latencia)
    $now = new DateTime();
    $now->modify('-1 minute');
    if ($start_dt < $now) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Start date/time must be in the future',
            'message_ca' => 'La data/hora d\'inici ha de ser futura',
            'message_es' => 'La fecha/hora de inicio debe ser futura',
            'message_en' => 'Start date/time must be in the future'
        ]);
        exit();
    }
    
    // Check if vehicle exists and get details
    $stmt = $db->prepare("
        SELECT id, model, status, price_per_minute 
        FROM vehicles 
        WHERE id = ?
    ");
    $stmt->execute([$vehicle_id]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vehicle) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Vehicle not found',
            'message_ca' => 'Vehicle no trobat',
            'message_es' => 'Vehículo no encontrado',
            'message_en' => 'Vehicle not found'
        ]);
        exit();
    }
    
    // Check if vehicle is available (not in maintenance)
    if ($vehicle['status'] === 'maintenance') {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Vehicle is currently under maintenance',
            'message_ca' => 'El vehicle està en manteniment',
            'message_es' => 'El vehículo está en mantenimiento',
            'message_en' => 'Vehicle is currently under maintenance'
        ]);
        exit();
    }
    
    // Check for booking conflicts (prevent double-booking)
    $stmt = $db->prepare("
        SELECT id 
        FROM bookings 
        WHERE vehicle_id = ? 
        AND status IN ('confirmed', 'active', 'pending')
        AND (
            (start_datetime <= ? AND end_datetime >= ?) OR
            (start_datetime <= ? AND end_datetime >= ?) OR
            (start_datetime >= ? AND end_datetime <= ?)
        )
    ");
        'issssss',
        $vehicle_id,
        $start_datetime, $start_datetime,
        $end_datetime, $end_datetime,
        $start_datetime, $end_datetime
    );
    $stmt->execute();
    // $result = $stmt; // PDO: stmt ya contiene resultados
    
    if ($result->rowCount() > 0) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Vehicle is already booked for the selected time period',
            'message_ca' => 'El vehicle ja està reservat per al període seleccionat',
            'message_es' => 'El vehículo ya está reservado para el período seleccionado',
            'message_en' => 'Vehicle is already booked for the selected time period'
        ]);
        exit();
    }
    
    // Calculate total cost based on duration and price per minute
    $duration_minutes = ($end_dt->getTimestamp() - $start_dt->getTimestamp()) / 60;
    $price_per_minute = floatval($vehicle['price_per_minute']);
    $unlock_fee = 0.50; // Fixed unlock fee
    $total_cost = ($duration_minutes * $price_per_minute) + $unlock_fee;
    $total_cost = round($total_cost, 2);
    
    // Insert booking record
    $stmt = $db->prepare("
        INSERT INTO bookings (
            user_id, 
            vehicle_id, 
            start_datetime, 
            end_datetime, 
            total_cost, 
            status
        ) VALUES (?, ?, ?, ?, ?, 'confirmed')
    ");
        'iissd',
        $user_id,
        $vehicle_id,
        $start_datetime,
        $end_datetime,
        $total_cost
    );
    
    if ($stmt->execute()) {
        $booking_id = $stmt->insert_id;
        
        // Update vehicle status to reserved
        $stmt = $db->prepare("UPDATE vehicles SET status = 'reserved' WHERE id = ?");
        $stmt->execute([$vehicle_id]);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Booking confirmed successfully',
            'message_ca' => 'Reserva confirmada correctament',
            'message_es' => 'Reserva confirmada correctamente',
            'message_en' => 'Booking confirmed successfully',
            'booking' => [
                'id' => $booking_id,
                'vehicle_id' => $vehicle_id,
                'vehicle_model' => $vehicle['model'],
                'start_datetime' => $start_datetime,
                'end_datetime' => $end_datetime,
                'duration_minutes' => round($duration_minutes, 0),
                'price_per_minute' => $price_per_minute,
                'unlock_fee' => $unlock_fee,
                'total_cost' => $total_cost,
                'status' => 'confirmed'
            ]
        ]);
    } else {
        throw new Exception('Failed to create booking');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'message_ca' => 'Error del servidor',
        'message_es' => 'Error del servidor',
        'message_en' => 'Server error'
    ]);
}
