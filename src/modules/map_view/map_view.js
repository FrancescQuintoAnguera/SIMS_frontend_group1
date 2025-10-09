// map_view.js — Clean implementation: SVG markers, selection, and geolocation
document.addEventListener('DOMContentLoaded', () => {
  const cars = window.CARS || [
    { id: 1, lat: 40.4168, lng: -3.7038 },
    { id: 2, lat: 40.4198, lng: -3.6950 },
    { id: 3, lat: 40.4120, lng: -3.7100 }
  ];

  // Initialize the map. If cars array exists, center on the first car.
  const map = L.map('map').setView(cars.length ? [cars[0].lat, cars[0].lng] : [40.4168, -3.7038], 13);
  // Add OpenStreetMap tiles (public tile server)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap contributors' }).addTo(map);

  const markersLayer = L.layerGroup().addTo(map);

  // Create a custom DivIcon that contains an inline SVG. We use inline SVG
  // so the marker can be styled via CSS (using currentColor for stroke).
  function createCarIcon(id) {
    const svg = `
      <svg width="30" height="42" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" data-car-id="${id}" aria-hidden="true" focusable="false">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
      </svg>`;
    return L.divIcon({ className: 'car-marker', html: svg, iconSize: [30, 42], iconAnchor: [15, 42] });
  }

  // Keep references to markers by id to easily toggle selection state.
  const markerById = new Map();
  let selectedId = null; // currently selected car id (or null)

  cars.forEach(car => {
    const marker = L.marker([car.lat, car.lng], { icon: createCarIcon(car.id) }).addTo(markersLayer);
    markerById.set(car.id, marker);
    // Click handler: toggle selection class and expose selected id globally
    marker.on('click', () => {
      const el = marker.getElement();
      if (!el) return;
      // Deselect if already selected
      if (selectedId === car.id) {
        el.classList.remove('selected');
        selectedId = null;
        window.SELECTED_CAR_ID = null;
        return;
      }
      // Remove previous selection
      if (selectedId !== null) {
        const prev = markerById.get(selectedId);
        if (prev && prev.getElement()) prev.getElement().classList.remove('selected');
      }
      // Set new selection
      el.classList.add('selected');
      selectedId = car.id;
      window.SELECTED_CAR_ID = car.id;
    });
  });

  // Geolocation control + auto-locate
  // Small custom control in the top-left to trigger browser geolocation
  const locateControl = L.control({ position: 'topleft' });
  locateControl.onAdd = () => {
    const div = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
    const a = L.DomUtil.create('a', '', div);
    a.href = '#'; a.title = 'My location'; a.innerHTML = 'Aqui estoy yo';
    L.DomEvent.on(a, 'click', e => { L.DomEvent.stop(e); map.locate({ setView: true, maxZoom: 16 }); });
    return div;
  };
  locateControl.addTo(map);

  // When location is found, show a small circle marker and open a popup
  let userMarker = null;
  map.on('locationfound', e => {
    if (userMarker) userMarker.remove();
    userMarker = L.circleMarker(e.latlng, { radius: 8, color: '#2A9D8F', fillColor: '#2A9D8F', fillOpacity: 0.9 }).addTo(map);
    userMarker.bindPopup('You are here').openPopup();
  });
  map.on('locationerror', err => console.warn('locationerror', err));

  // Try auto-locate (Permissions API when available)
  // Try to auto-locate on load. Use Permissions API when available to avoid
  // aggressive prompts if the user has denied access previously.
  (function autoLocate() {
    if (navigator.permissions && navigator.permissions.query) {
      navigator.permissions.query({ name: 'geolocation' }).then(r => { if (r.state !== 'denied') map.locate({ setView: true, maxZoom: 16 }); }).catch(() => map.locate({ setView: true, maxZoom: 16 }));
    } else if (navigator.geolocation) {
      map.locate({ setView: true, maxZoom: 16 });
    }
  })();
});
