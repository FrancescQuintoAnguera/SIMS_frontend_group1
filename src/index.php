<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// RUTA CORREGIDA: Apunta al nombre de archivo exacto
require 'controlers/mapcontroler.php';

// La clase sigue siendo MapController por convención de PHP
$controller = new MapController();

$route = $_GET['dir'] ?? 'map'; 

switch ($route) {
    case 'map':
        $controller->showPublicMap();
        break;

    default:
        $controller->showNotFound();
        break;
}