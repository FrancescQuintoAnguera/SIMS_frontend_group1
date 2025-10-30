<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/database.php';

// Check if super admin is authenticated
if (!isset($_SESSION['tenant_admin_id']) || !isset($_SESSION['is_super_admin']) || !$_SESSION['is_super_admin']) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$tenantManager = getTenantManager();

try {
    // Validate required fields
    $required = ['name', 'subdomain', 'contact_email', 'subscription_plan'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }

    // Prepare tenant data
    $tenantData = [
        'name' => $_POST['name'],
        'subdomain' => strtolower(trim($_POST['subdomain'])),
        'domain' => $_POST['domain'] ?? null,
        'contact_email' => $_POST['contact_email'],
        'contact_phone' => $_POST['contact_phone'] ?? null,
        'city' => $_POST['city'] ?? null,
        'country' => $_POST['country'] ?? null,
        'subscription_plan' => $_POST['subscription_plan'],
        'subscription_start' => date('Y-m-d'),
        'subscription_end' => date('Y-m-d', strtotime('+1 year')),
        'status' => $_POST['status'] ?? 'active'
    ];

    // Validate subdomain format
    if (!preg_match('/^[a-z0-9-]+$/', $tenantData['subdomain'])) {
        throw new Exception('Subdomain can only contain lowercase letters, numbers, and hyphens');
    }

    // Create tenant
    $tenantId = $tenantManager->createTenant($tenantData);

    // Create default admin user for the tenant
    $db = getDB();
    $defaultPassword = password_hash('changeme123', PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO users (tenant_id, email, username, password, fullname, is_admin) 
        VALUES (?, ?, ?, ?, ?, TRUE)
    ");
    
    $defaultEmail = 'admin@' . $tenantData['subdomain'] . '.com';
    $defaultUsername = 'admin_' . $tenantData['subdomain'];
    $defaultFullname = $tenantData['name'] . ' Administrator';
    
    $stmt->execute([
        $tenantId,
        $defaultEmail,
        $defaultUsername,
        $defaultPassword,
        $defaultFullname
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Tenant created successfully',
        'tenant_id' => $tenantId,
        'default_credentials' => [
            'username' => $defaultUsername,
            'password' => 'changeme123',
            'note' => 'Please change this password immediately'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
