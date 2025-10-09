// Inicializar el mapa centrado en Madrid
const map = L.map('map').setView([40.4168, -3.7038], 13);

// Añadir la capa base de OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Añadir un marcador con popup
const marker = L.marker([40.4168, -3.7038]).addTo(map);
marker.bindPopup("<b>Madrid</b><br>Capital de España").openPopup();
