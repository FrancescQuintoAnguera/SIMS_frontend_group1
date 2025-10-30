<?php
header('Content-Type: application/json');
session_start();

$authenticated = isset($_SESSION['tenant_admin_id']) && 
                 isset($_SESSION['is_super_admin']) && 
                 $_SESSION['is_super_admin'] === true;

echo json_encode([
    'authenticated' => $authenticated,
    'admin_id' => $_SESSION['tenant_admin_id'] ?? null
]);
