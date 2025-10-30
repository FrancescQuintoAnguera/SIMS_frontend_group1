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
    // Get all tenant admins with their access count
    $result = $db->query("
        SELECT 
            ta.id,
            ta.username,
            ta.email,
            ta.fullname,
            ta.phone,
            ta.is_super_admin,
            ta.last_login,
            ta.created_at,
            (SELECT COUNT(*) FROM tenant_admin_access WHERE tenant_admin_id = ta.id) as tenant_count
        FROM tenant_admins ta
        ORDER BY ta.created_at DESC
    ");

    $admins = [];
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }

    echo json_encode([
        'success' => true,
        'admins' => $admins
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
