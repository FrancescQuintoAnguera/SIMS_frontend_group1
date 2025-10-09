<?php
// Datos de los coches
$cars = [
  ["id" => 1, "lat" => 40.709100, "lng" => 0.582300],
  ["id" => 2, "lat" => 40.709795, "lng" => 0.576714],
  ["id" => 3, "lat" => 40.705097, "lng" => 0.576183]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mapa - Ubicación de coches</title>
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <!-- Tu CSS -->
  <link rel="stylesheet" href="../styles/map_view.css" />
</head>
<body>
  <div id="map"></div>

  <!-- Inyectar datos de los coches -->
  <script>
    window.CARS = <?php echo json_encode($cars, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK); ?>;
  </script>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <!-- Tu JS -->
  <script src="../scripts/map_view.js"></script>
</body>
</html>
