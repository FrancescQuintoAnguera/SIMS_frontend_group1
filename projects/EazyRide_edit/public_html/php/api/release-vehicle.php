<?php
header('Content-Type: application/json');
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No autenticat'
    ]);
    exit;
}

require_once __DIR__ . '/../core/DatabaseMariaDB.php';

try {
    $userId = $_SESSION['user_id'];
    $db = DatabaseMariaDB::getConnection();
    
    // Buscar booking activo del usuario
    $stmt = $db->prepare("
        SELECT b.id as booking_id, b.vehicle_id 
        FROM bookings b
        WHERE b.user_id = ?
        AND b.status IN ('active', 'confirmed')
        ORDER BY b.start_datetime DESC
        LIMIT 1
    ");
    
    $stmt->execute([$userId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($booking) {
        // Iniciar transacción
        $db->beginTransaction();
        
        try {
            // Actualizar estado del booking
            $stmt = $db->prepare("
                UPDATE bookings 
                SET status = 'completed', 
                    end_datetime = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$booking['booking_id']]);
            
            // Actualizar estado del vehículo a disponible
            $stmt = $db->prepare("
                UPDATE vehicles 
                SET status = 'available' 
                WHERE id = ?
            ");
            $stmt->execute([$booking['vehicle_id']]);
            
            // Commit de la transacción
            $db->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Reserva finalitzada correctament'
            ]);
        } catch (Exception $e) {
            // Rollback en caso de error
            $db->rollback();
            throw $e;
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No tens cap reserva activa'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al finalitzar la reserva',
        'error' => $e->getMessage()
    ]);
}
?>
