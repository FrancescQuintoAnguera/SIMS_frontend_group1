<?php
// Datos de los coches
$cars = [
    ["id" => 1, "lat" => 40.709100, "lng" => 0.582300],
    ["id" => 2, "lat" => 40.709795, "lng" => 0.576714],
    ["id" => 3, "lat" => 40.705097, "lng" => 0.576183],
    ["id" => 4, "lat" => 40.707802, "lng" => 0.576544],
    ["id" => 5, "lat" => 40.7061292, "lng" => 0.578527]
];

// Cargar URL de OpenStreetMap desde .env
$envFile = __DIR__ . '/../../../../.env';
$osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if ($key === 'URL_OPENSTREETMAP') {
            $osmUrl = $value;
            break;
        }
    }
}
?>

<!-- Map container -->
<div id="map"></div>

<script>
window.CARS = <?php echo json_encode($cars, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK); ?>;
window.OSM_TILE_URL = <?php echo json_encode($osmUrl); ?>;
</script>