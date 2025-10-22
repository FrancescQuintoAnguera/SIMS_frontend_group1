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

<div id="map"></div>

<script>
(function() {
  console.log('[MAP] Iniciando carga del mapa');
  
  window.CARS = <?php echo json_encode($cars, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK); ?>;
  window.OSM_TILE_URL = <?php echo json_encode($osmUrl); ?>;
  
  console.log('[MAP] Datos configurados:', { cars: window.CARS, osmUrl: window.OSM_TILE_URL });

  function loadLeaflet() {
    return new Promise((resolve, reject) => {
      if (window.L) {
        console.log('[MAP] Leaflet ya está cargado');
        resolve();
        return;
      }

      console.log('[MAP] Cargando Leaflet CSS...');
      const cssLink = document.createElement('link');
      cssLink.rel = 'stylesheet';
      cssLink.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
      document.head.appendChild(cssLink);

      const mapCssLink = document.createElement('link');
      mapCssLink.rel = 'stylesheet';
      mapCssLink.href = '/common/modules/map_view/styles/map_view.css';
      document.head.appendChild(mapCssLink);

      console.log('[MAP] Cargando Leaflet JS...');
      const script = document.createElement('script');
      script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
      script.onload = () => {
        console.log('[MAP] Leaflet JS cargado correctamente');
        resolve();
      };
      script.onerror = (err) => {
        console.error('[MAP] Error cargando Leaflet JS:', err);
        reject(err);
      };
      document.body.appendChild(script);
    });
  }

  function loadMapScript() {
    return new Promise((resolve, reject) => {
      console.log('[MAP] Cargando map_view.js...');
      const script = document.createElement('script');
      script.src = '/common/modules/map_view/scripts/map_view.js';
      script.onload = () => {
        console.log('[MAP] map_view.js cargado correctamente');
        resolve();
      };
      script.onerror = (err) => {
        console.error('[MAP] Error cargando map_view.js:', err);
        reject(err);
      };
      document.body.appendChild(script);
    });
  }

  loadLeaflet()
    .then(() => {
      console.log('[MAP] Leaflet listo, cargando script del mapa...');
      return loadMapScript();
    })
    .then(() => {
      console.log('[MAP] Mapa inicializado completamente');
    })
    .catch(err => {
      console.error('[MAP] Error fatal cargando el mapa:', err);
    });
})();
</script>