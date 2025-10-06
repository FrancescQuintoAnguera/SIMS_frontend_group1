<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mapa con Leaflet y OpenStreetMap</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <style>
    #map {
      height: 100vh;
      width: 100%;
      margin: 0;
      padding: 0;
    }
    body {
      margin: 0;
      font-family: Arial, sans-serif;
    }
  </style>
</head>
<body>
  <div id="map"></div>

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
    // Crea el mapa centrado en una ubicación inicial (por ejemplo, Ciudad de México)
    const map = L.map('map').setView([19.4326, -99.1332], 12);

    // Capa base de OpenStreetMap (gratuita)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Puedes añadir un marcador si quieres
    L.marker([19.4326, -99.1332])
      .addTo(map)
      .bindPopup('Centro de la Ciudad de México')
      .openPopup();
  </script>
</body>
</html>
