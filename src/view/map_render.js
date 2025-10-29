// view/map_render.js

// Función para obtener el color del borde basado en el estado
function getBorderColor(status) {
    switch (status) {
        case 'active':
            return '#FFA500'; // Naranja
        case 'inactive':
            return '#008000'; // Verde
        case 'broken':
            return '#FF0000'; // Rojo
        default:
            return '#6c46a6'; // Morado (Default)
    }
}

// 1. Initialize the map
const map = L.map('leafletMap').setView([40.4168, -3.7038], 6);

// 2. Add the OpenStreetMap base layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

// 4. Draw charging points
if (typeof chargingPoints !== 'undefined' && chargingPoints.length > 0) {
    let bounds = [];

    chargingPoints.forEach(point => {
        const lat = point.latitude; 
        const lng = point.longitude;
        const status = point.status ? point.status.toLowerCase() : 'default'; 
        const borderColor = getBorderColor(status); 

        if (lat && lng) {
            
            // 3. Icono Personalizado (Se crea dinámicamente para cada punto)
            const CustomCarIcon = L.divIcon({
                className: 'custom-div-icon', 
                html: `
                    <div class="marker-circle-custom" style="border-color: ${borderColor};">
                        <img src="view/coche-punto.png" alt="Charging Point">
                    </div>
                `,
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                popupAnchor: [0, -40]
            });

            const marker = L.marker([lat, lng], { icon: CustomCarIcon })
                .addTo(map)
                .bindPopup(`
                    <b>${point.name}</b><br>
                    Status: ${status.toUpperCase()}
                `);
            
            bounds.push([lat, lng]);
        }
    });

    // Adjust map to fit all markers
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }
}