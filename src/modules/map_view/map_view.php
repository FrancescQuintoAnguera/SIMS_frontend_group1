<?php
// map_view.php — Page that serves the map view and injects initial car data
// - This file provides a small example dataset in $cars and writes it to
//   `window.CARS` so the client-side JS can read it.
// - In a real app you would fetch these records from a database or REST API.

$cars = [
  ["id" => 1, "lat" => 40.709108, "lng" => 0.582408],
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
  <link rel="stylesheet" href="map_view.css" />
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
  <script src="map_view.js"></script>
</body>
</html>
