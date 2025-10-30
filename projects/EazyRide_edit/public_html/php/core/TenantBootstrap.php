<?php
/**
 * Tenant Bootstrap - Initialize tenant context for all requests
 * Include this file at the beginning of every tenant-aware PHP file
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load database and tenant manager
require_once __DIR__ . '/../../config/database.php';

// Initialize tenant context
$tenantManager = getTenantManager();
$currentTenant = $tenantManager->identifyTenant();

// If no tenant identified and this is not a super admin route, show error
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$isSuperAdminRoute = strpos($requestUri, 'superadmin') !== false || strpos($requestUri, '/api/superadmin/') !== false;

if (!$currentTenant && !$isSuperAdminRoute) {
    // Try to get tenant from parameter for development
    if (isset($_GET['tenant']) || isset($_POST['tenant'])) {
        $tenantIdentifier = $_GET['tenant'] ?? $_POST['tenant'];
        $currentTenant = $tenantManager->identifyTenant($tenantIdentifier);
    }
    
    if (!$currentTenant) {
        // Show tenant selection page or error
        http_response_code(404);
        die('
            <!DOCTYPE html>
            <html>
            <head>
                <title>Tenant Not Found</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                        margin: 0;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    }
                    .error-box {
                        background: white;
                        padding: 40px;
                        border-radius: 12px;
                        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                        text-align: center;
                        max-width: 500px;
                    }
                    h1 { color: #333; margin-bottom: 20px; }
                    p { color: #666; margin-bottom: 30px; }
                    .btn {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 12px 24px;
                        border-radius: 8px;
                        text-decoration: none;
                        display: inline-block;
                    }
                </style>
            </head>
            <body>
                <div class="error-box">
                    <h1>🏢 Tenant Not Found</h1>
                    <p>We could not identify your organization. Please check your URL or contact support.</p>
                    <a href="/superadmin-login.html" class="btn">Go to Super Admin</a>
                </div>
            </body>
            </html>
        ');
    }
}

// Set tenant context globally for easy access
define('CURRENT_TENANT_ID', $currentTenant ? $currentTenant['id'] : null);
define('CURRENT_TENANT_NAME', $currentTenant ? $currentTenant['name'] : null);
define('CURRENT_TENANT_SUBDOMAIN', $currentTenant ? $currentTenant['subdomain'] : null);

// Helper function to get current tenant
function getCurrentTenant() {
    global $tenantManager;
    return $tenantManager->getCurrentTenant();
}

// Helper function to get tenant-aware database
function getTDB() {
    return getTenantDB();
}

// Helper function to check if user belongs to current tenant
function checkTenantAccess($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT tenant_id FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    
    if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        return $row['tenant_id'] == CURRENT_TENANT_ID;
    }
    
    return false;
}

// Helper function to verify tenant context in session
function verifyTenantSession() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    if (!checkTenantAccess($_SESSION['user_id'])) {
        session_destroy();
        return false;
    }
    
    return true;
}
