<?php
// map_view.php â€” Page that serves the map view and injects initial car data

$cars = [
  ["id" => 1, "lat" => 40.709100, "lng" => 0.582300],
  ["id" => 2, "lat" => 40.709795, "lng" => 0.576714],
  ["id" => 3, "lat" => 40.705097, "lng" => 0.576183]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Map - Car locations</title>
  <!-- Leaflet CSS from CDN -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="../styles/map_view.css" />
  <!-- Bottom sheet styles -->
  <link rel="stylesheet" href="../../common/components/bottomSheet/bottomSheet.css" />
</head>
<body>
  <!-- Map container: Leaflet will render into this element -->
  <div id="map"></div>

  <script>
    // Inject server-side data into the global window object so client JS can use it
    window.CARS = <?php echo json_encode($cars, JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK); ?>;
  </script>

  <!-- Leaflet core script and the module's script -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <!-- Bottom sheet script (loaded before map_view.js) -->
  <script src="../../common/components/bottomSheet/bottomSheet.js"></script>
  <script src="map_view.js"></script>
  <div id="bottom-sheet" class="bottom-sheet">
  <img src="coche.png" alt="Coche">
  <div class="info">
    <p id="matricula">Matrícula: </p>
    <p id="bateria">Batería: </p>
  </div>
  <button id="reservar">Reservar</button>
  <button id="ayuda">Ayuda</button>
</div>

</body>
</html>
