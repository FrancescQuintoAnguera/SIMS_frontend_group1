document.addEventListener('DOMContentLoaded', () => {
    const mapContainer = document.getElementById('adminMap');
    const latInput = document.getElementById('topCoord'); 
    const lngInput = document.getElementById('leftCoord');
    
    let map;
    let tempMarkerInstance = null; 

    const AdminCustomCarIcon = L.divIcon({
        className: 'admin-custom-div-icon',
        html: `
            <div class="marker-circle-custom" style="border-color: #ff0000;"> 
                <img src="view/coche-punto.png" alt="New Point">
            </div>
        `,
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40]
    });
    
    if (mapContainer) {
        // Coordenadas iniciales centradas en España
        map = L.map('adminMap').setView([40.4168, -3.7038], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        map.on('click', (e) => {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);

            latInput.value = lat;
            lngInput.value = lng;
            
            if (tempMarkerInstance) {
                map.removeLayer(tempMarkerInstance);
            }

            tempMarkerInstance = L.marker(e.latlng, { icon: AdminCustomCarIcon })
                .addTo(map)
                .bindPopup("New Charging Point").openPopup();

            console.log(`Coordinates captured: LAT: ${lat}, LNG: ${lng}`);
        });
    } else {
        console.error("Map container (#adminMap) not found.");
    }
});