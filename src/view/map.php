<?php 
$chargingPoints = $chargingPoints ?? []; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location Map</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <link rel="stylesheet" href="view/map.css"> 
</head>
<body>
    <div class="map-container" id="leafletMap">
    </div>

    <script>
        const chargingPoints = <?php echo json_encode($chargingPoints); ?>;
    </script>
    
    <script src="view/map_render.js" defer></script> 
</body>
</html>