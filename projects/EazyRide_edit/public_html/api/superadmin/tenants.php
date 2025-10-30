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
    // Get all tenants with statistics
    $result = $db->query("
        SELECT 
            t.id,
            t.name,
            t.subdomain,
            t.domain,
            t.contact_email,
            t.contact_phone,
            t.city,
            t.country,
            t.status,
            t.subscription_plan,
            t.subscription_start,
            t.subscription_end,
            t.created_at,
            (SELECT COUNT(*) FROM users WHERE tenant_id = t.id) as total_users,
            (SELECT COUNT(*) FROM vehicles WHERE tenant_id = t.id) as total_vehicles,
            (SELECT COUNT(*) FROM bookings WHERE tenant_id = t.id) as total_bookings,
            (SELECT SUM(total_cost) FROM bookings WHERE tenant_id = t.id AND status = 'completed') as total_revenue
        FROM tenants t
        ORDER BY t.created_at DESC
    ");

    $tenants = [];
    while ($row = $result->fetch_assoc()) {
        $tenants[] = $row;
    }

    echo json_encode([
        'success' => true,
        'tenants' => $tenants
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
