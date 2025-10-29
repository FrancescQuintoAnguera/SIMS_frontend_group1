<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'controlers/mapcontroler.php';

$controller = new MapController();

$route = $_GET['dir'] ?? 'map'; 

switch ($route) {
    case 'map':
        $controller->showPublicMap();
        break;
    
    // CASO 'ADMIN' RESTAURADO
    case 'admin':
        $controller->showAdminCrud();
        break;

    default:
        $controller->showNotFound();
        break;
}