// views/map_render.js

// 1. Initialize the map
const map = L.map('leafletMap').setView([40.4168, -3.7038], 6);

// 2. Add the OpenStreetMap base layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

// 3. Custom Icon
const CustomCarIcon = L.divIcon({
    className: 'custom-div-icon', 
    html: `
        <div class="marker-circle-custom">
            <img src="../public/img/car-point.png" alt="Charging Point">
        </div>
    `,
    iconSize: [40, 40],
    iconAnchor: [20, 40],
    popupAnchor: [0, -40]
});


// 4. Draw charging points
if (typeof chargingPoints !== 'undefined' && chargingPoints.length > 0) {
    let bounds = [];

    chargingPoints.forEach(point => {
        const lat = point.latitude; 
        const lng = point.longitude;

        if (lat && lng) {
            const marker = L.marker([lat, lng], { icon: CustomCarIcon })
                .addTo(map)
                .bindPopup(`<b>${point.name}</b><br>ID: ${point.id}`);
            
            bounds.push([lat, lng]);
        }
    });

    // Adjust map to fit all markers
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }
}