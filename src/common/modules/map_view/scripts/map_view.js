document.addEventListener('DOMContentLoaded', () => {
  const cars = (window.CARS && Array.isArray(window.CARS)) 
    ? window.CARS 
    : [
        { id: 1, lat: 40.709100, lng: 0.582300 },
        { id: 2, lat: 40.709795, lng: 0.576714 },
        { id: 3, lat: 40.705097, lng: 0.576183 },
        { id: 4, lat: 40.707802, lng: 0.576544 },
        { id: 5, lat: 40.706129, lng: 0.578527 }
      ];

  const osmUrl = window.OSM_TILE_URL || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
  const map = L.map('map').setView([cars[0].lat, cars[0].lng], 13);

  L.tileLayer(osmUrl, {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  const markersLayer = L.layerGroup().addTo(map);
  const markerById = new Map();
  let selectedId = null;

  function createCarIcon(id) {
    const svg = `
      <svg width="30" height="42" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" data-car-id="${id}">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
      </svg>`;
    return L.divIcon({ 
      className: 'car-marker', 
      html: svg, 
      iconSize: [30, 42], 
      iconAnchor: [15, 42] 
    });
  }

  function handleMarkerClick(car, marker) {
    const el = marker.getElement();
    if (!el) return;

    if (selectedId === car.id) {
      el.classList.remove('selected');
      selectedId = null;
      window.SELECTED_CAR_ID = null;
      return;
    }

    if (selectedId !== null) {
      const prevMarker = markerById.get(selectedId);
      const prevEl = prevMarker?.getElement();
      if (prevEl) prevEl.classList.remove('selected');
    }

    el.classList.add('selected');
    selectedId = car.id;
    window.SELECTED_CAR_ID = car.id;
  }

  cars.forEach(car => {
    const marker = L.marker([car.lat, car.lng], { 
      icon: createCarIcon(car.id) 
    }).addTo(markersLayer);

    marker.bindPopup(`ID: ${car.id}`);
    markerById.set(car.id, marker);
    marker.on('click', () => handleMarkerClick(car, marker));
  });

  const locateControl = L.control({ position: 'topleft' });
  
  locateControl.onAdd = () => {
    const div = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-locate');
    const button = L.DomUtil.create('a', '', div);
    
    button.href = '#';
    button.title = 'Mi ubicación';
    button.innerHTML = 'Yo';
    
    L.DomEvent.on(button, 'click', (e) => {
      L.DomEvent.stopPropagation(e);
      L.DomEvent.preventDefault(e);
      map.locate({ setView: true, maxZoom: 16 });
    });
    
    return div;
  };
  
  locateControl.addTo(map);

  let userMarker = null;

  map.on('locationfound', (e) => {
    if (userMarker) userMarker.remove();
    
    userMarker = L.circleMarker(e.latlng, {
      radius: 8,
      color: '#2A9D8F',
      fillColor: '#2A9D8F',
      fillOpacity: 0.9
    }).addTo(map);
    
    userMarker.bindPopup('Aquí estoy').openPopup();
  });

  map.on('locationerror', (err) => {
    console.warn('Error de geolocalización:', err);
  });

  (function autoLocate() {
    if (navigator.permissions && navigator.permissions.query) {
      navigator.permissions.query({ name: 'geolocation' })
        .then(result => {
          if (result.state !== 'denied') {
            map.locate({ setView: true, maxZoom: 16 });
          }
        })
        .catch(() => {
          map.locate({ setView: true, maxZoom: 16 });
        });
    } else if (navigator.geolocation) {
      map.locate({ setView: true, maxZoom: 16 });
    }
  })();
});
