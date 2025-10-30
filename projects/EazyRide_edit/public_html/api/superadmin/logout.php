<?php
session_start();

require_once __DIR__ . '/../../config/database.php';

$tenantManager = getTenantManager();
$tenantManager->clearTenantContext();

session_destroy();

header('Location: ../../superadmin-login.html');
exit;
