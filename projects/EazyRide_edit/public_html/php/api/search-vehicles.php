<?php
/**
 * Search Vehicles API Endpoint
 * Advanced vehicle search with multiple filters and availability checking
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit();
}

require_once __DIR__ . '/../core/DatabaseMariaDB.php';

try {
    $db = DatabaseMariaDB::getConnection();
    
    // Get search parameters
    $vehicle_type = $_GET['vehicle_type'] ?? null;
    $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
    $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
    $lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
    $lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
    $max_distance = isset($_GET['max_distance']) ? floatval($_GET['max_distance']) : null;
    $start_datetime = $_GET['start_datetime'] ?? null;
    $end_datetime = $_GET['end_datetime'] ?? null;
    $min_battery = isset($_GET['min_battery']) ? intval($_GET['min_battery']) : null;
    $accessibility = isset($_GET['accessibility']) && $_GET['accessibility'] === 'true';
    $sort_by = $_GET['sort_by'] ?? 'battery'; // Options: price, distance, battery
    
    // Build base query
    $query = "SELECT DISTINCT
        v.id,
        v.plate,
        v.brand,
        v.model,
        v.year,
        v.battery_level,
        v.latitude,
        v.longitude,
        v.status,
        v.vehicle_type,
        v.is_accessible,
        v.accessibility_features,
        v.price_per_minute,
        v.image_url";
    
    // Add distance calculation if location provided
    if ($lat !== null && $lng !== null) {
        $query .= ",
        (6371 * acos(cos(radians(?)) * cos(radians(v.latitude)) * 
        cos(radians(v.longitude) - radians(?)) + sin(radians(?)) * 
        sin(radians(v.latitude)))) AS distance";
    }
    
    $query .= " FROM vehicles v";
    
    // Add booking check if date/time range provided
    if ($start_datetime && $end_datetime) {
        $query .= " LEFT JOIN bookings b ON v.id = b.vehicle_id 
            AND b.status IN ('confirmed', 'active', 'pending')
            AND (
                (b.start_datetime <= ? AND b.end_datetime >= ?) OR
                (b.start_datetime <= ? AND b.end_datetime >= ?) OR
                (b.start_datetime >= ? AND b.end_datetime <= ?)
            )";
    }
    
    $query .= " WHERE v.status != 'maintenance'";
    
    // Prepare parameters array
    $params = [];
    $types = '';
    
    // Add location parameters for distance calculation
    if ($lat !== null && $lng !== null) {
        $params[] = $lat;
        $params[] = $lng;
        $params[] = $lat;
        $types .= 'ddd';
    }
    
    // Add date/time parameters for availability check
    if ($start_datetime && $end_datetime) {
        $params[] = $start_datetime;
        $params[] = $start_datetime;
        $params[] = $end_datetime;
        $params[] = $end_datetime;
        $params[] = $start_datetime;
        $params[] = $end_datetime;
        $types .= 'ssssss';
        
        // Exclude vehicles with conflicting bookings
        $query .= " AND b.id IS NULL";
    }
    
    // Apply vehicle type filter
    if ($vehicle_type) {
        $query .= " AND v.vehicle_type = ?";
        $params[] = $vehicle_type;
        $types .= 's';
    }
    
    // Apply price range filter
    if ($min_price !== null) {
        $query .= " AND v.price_per_minute >= ?";
        $params[] = $min_price;
        $types .= 'd';
    }
    
    if ($max_price !== null) {
        $query .= " AND v.price_per_minute <= ?";
        $params[] = $max_price;
        $types .= 'd';
    }
    
    // Apply battery level filter
    if ($min_battery !== null) {
        $query .= " AND v.battery_level >= ?";
        $params[] = $min_battery;
        $types .= 'i';
    }
    
    // Apply accessibility filter
    if ($accessibility) {
        $query .= " AND v.is_accessible = 1";
    }
    
    // Apply distance filter if location provided
    if ($lat !== null && $lng !== null && $max_distance !== null) {
        $query .= " HAVING distance <= ?";
        $params[] = $max_distance;
        $types .= 'd';
    }
    
    // Apply sorting
    switch ($sort_by) {
        case 'price':
            $query .= " ORDER BY v.price_per_minute ASC, v.battery_level DESC";
            break;
        case 'distance':
            if ($lat !== null && $lng !== null) {
                $query .= " ORDER BY distance ASC, v.battery_level DESC";
            } else {
                $query .= " ORDER BY v.battery_level DESC";
            }
            break;
        case 'battery':
        default:
            $query .= " ORDER BY v.battery_level DESC";
            if ($lat !== null && $lng !== null) {
                $query .= ", distance ASC";
            }
            break;
    }
    
    // Prepare and execute query
    $stmt = $db->prepare($query);
    
    if (!empty($params)) {
        // bind_param removed - use array params in execute()
    }
    
    $stmt->execute();
    // $result = $stmt; // PDO: stmt ya contiene resultados
    
    // Build results array
    $vehicles = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $vehicle = [
            'id' => (int)$row['id'],
            'plate' => $row['plate'],
            'brand' => $row['brand'],
            'model' => $row['model'],
            'year' => (int)$row['year'],
            'battery' => (int)$row['battery_level'],
            'location' => [
                'lat' => (float)$row['latitude'],
                'lng' => (float)$row['longitude']
            ],
            'status' => $row['status'],
            'type' => $row['vehicle_type'],
            'is_accessible' => (bool)$row['is_accessible'],
            'accessibility_features' => $row['accessibility_features'] ? json_decode($row['accessibility_features'], true) : [],
            'price_per_minute' => (float)$row['price_per_minute'],
            'image_url' => $row['image_url']
        ];
        
        // Add distance if calculated
        if (isset($row['distance'])) {
            $vehicle['distance'] = round((float)$row['distance'], 2);
        }
        
        // Add availability status
        $vehicle['available'] = true;
        
        $vehicles[] = $vehicle;
    }
    
    // Return results
    echo json_encode([
        'success' => true,
        'vehicles' => $vehicles,
        'count' => count($vehicles),
        'filters' => [
            'vehicle_type' => $vehicle_type,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'location' => ($lat !== null && $lng !== null) ? ['lat' => $lat, 'lng' => $lng] : null,
            'max_distance' => $max_distance,
            'start_datetime' => $start_datetime,
            'end_datetime' => $end_datetime,
            'min_battery' => $min_battery,
            'accessibility' => $accessibility,
            'sort_by' => $sort_by
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
