<?php

class MapController {

    public function __construct() {
    }

    public function showPublicMap() {
        $chargingPoints = [
            [
                "id" => 102,
                "name" => "Institute",
                "latitude" => 40.709136,
                "longitude" => 0.582554
            ]
        ];

        // RUTA FINAL CORREGIDA: Sube un nivel (..) y entra en la carpeta 'view' (singular)
        require __DIR__ . '/../view/map.php'; 
    }

    public function showNotFound() {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404</h1><p>Page Not Found.</p>";
    }
}