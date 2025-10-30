<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/database.php';

// Check if super admin is authenticated
if (!isset($_SESSION['tenant_admin_id']) || !isset($_SESSION['is_super_admin']) || !$_SESSION['is_super_admin']) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$tenantManager = getTenantManager();
$db = getDB();

try {
    // Get total tenants
    $result = $db->query("SELECT COUNT(*) as count FROM tenants");
    $totalTenants = $result->fetch_assoc()['count'];

    // Get active tenants
    $result = $db->query("SELECT COUNT(*) as count FROM tenants WHERE status = 'active'");
    $activeTenants = $result->fetch_assoc()['count'];

    // Get total users across all tenants
    $result = $db->query("SELECT COUNT(*) as count FROM users");
    $totalUsers = $result->fetch_assoc()['count'];

    // Get total vehicles across all tenants
    $result = $db->query("SELECT COUNT(*) as count FROM vehicles");
    $totalVehicles = $result->fetch_assoc()['count'];

    // Get total bookings
    $result = $db->query("SELECT COUNT(*) as count FROM bookings");
    $totalBookings = $result->fetch_assoc()['count'];

    // Get total revenue
    $result = $db->query("SELECT SUM(total_cost) as revenue FROM bookings WHERE status IN ('completed', 'active')");
    $totalRevenue = $result->fetch_assoc()['revenue'] ?? 0;

    // Get recent activity (last 10 tenants)
    $result = $db->query("
        SELECT id, name, subdomain, status, created_at 
        FROM tenants 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    
    $recentTenants = [];
    while ($row = $result->fetch_assoc()) {
        $recentTenants[] = $row;
    }

    echo json_encode([
        'success' => true,
        'total_tenants' => $totalTenants,
        'active_tenants' => $activeTenants,
        'total_users' => $totalUsers,
        'total_vehicles' => $totalVehicles,
        'total_bookings' => $totalBookings,
        'total_revenue' => number_format($totalRevenue, 2),
        'recent_tenants' => $recentTenants
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
