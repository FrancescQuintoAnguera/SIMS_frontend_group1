/**
 * JavaScript para la página de localizar vehículos
 * Gestiona mapas, listados y reclamación de vehículos
 */

const VehicleLocator = {
    mobileMap: null,
    desktopMap: null,
    mobileMarkers: [],
    desktopMarkers: [],
    userLocation: null,
    vehicles: [],
    
    /**
     * Inicializar la página
     */
    async init() {
        console.log('🗺️ Inicializando localizador de vehículos...');
        
        // Obtener ubicación del usuario
        await this.getUserLocation();
        
        // Cargar vehículos
        await this.loadVehicles();
        
        // Inicializar mapas
        await this.initMaps();
        
        // Configurar UI
        this.setupUI();
        
        console.log('✅ Localizador inicializado');
    },
    
    /**
     * Obtener ubicación del usuario
     */
    async getUserLocation() {
        return new Promise((resolve) => {
            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.userLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        console.log('📍 Ubicación obtenida:', this.userLocation);
                        resolve(this.userLocation);
                    },
                    (error) => {
                        console.warn('⚠️ Error de geolocalización:', error);
                        // Usar ubicación por defecto (Amposta)
                        this.userLocation = { lat: 40.7117, lng: 0.5783 };
                        resolve(this.userLocation);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    }
                );
            } else {
                console.warn('⚠️ Geolocalización no soportada');
                this.userLocation = { lat: 40.7117, lng: 0.5783 };
                resolve(this.userLocation);
            }
        });
    },
    
    /**
     * Cargar vehículos
     */
    async loadVehicles() {
        try {
            if (this.userLocation) {
                this.vehicles = await Vehicles.getAvailableVehicles(this.userLocation);
            } else {
                this.vehicles = await Vehicles.getAvailableVehicles();
            }
            
            // Calcular distancia para cada vehículo
            if (this.userLocation) {
                this.vehicles.forEach(vehicle => {
                    if (vehicle.location) {
                        vehicle.distance = this.calculateDistance(
                            this.userLocation,
                            vehicle.location
                        );
                    }
                });
                
                // Ordenar por distancia
                this.vehicles.sort((a, b) => (a.distance || Infinity) - (b.distance || Infinity));
            }
            
            console.log('🚗 Vehículos cargados:', this.vehicles.length);
            
            // Actualizar listas
            this.updateVehicleLists();
            
        } catch (error) {
            console.error('❌ Error al cargar vehículos:', error);
            this.vehicles = [];
        }
    },
    
    /**
     * Calcular distancia entre dos puntos
     */
    calculateDistance(coord1, coord2) {
        const R = 6371; // Radio de la Tierra en km
        const dLat = this.toRad(coord2.lat - coord1.lat);
        const dLng = this.toRad(coord2.lng - coord1.lng);
        
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(this.toRad(coord1.lat)) * Math.cos(this.toRad(coord2.lat)) *
                  Math.sin(dLng / 2) * Math.sin(dLng / 2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    },
    
    toRad(degrees) {
        return degrees * (Math.PI / 180);
    },
    
    /**
     * Inicializar mapas
     */
    async initMaps() {
        // Mapa móvil
        const mapMobileContainer = document.getElementById('map');
        if (mapMobileContainer && !mapMobileContainer._leaflet_id) {
            this.mobileMap = L.map('map').setView([this.userLocation.lat, this.userLocation.lng], 14);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                minZoom: 10,
                maxZoom: 18
            }).addTo(this.mobileMap);
            
            this.addUserMarker(this.mobileMap);
            this.addVehicleMarkers(this.mobileMap, this.mobileMarkers);
        }
        
        // Mapa desktop
        const mapDesktopContainer = document.getElementById('map-desktop');
        if (mapDesktopContainer && !mapDesktopContainer._leaflet_id) {
            this.desktopMap = L.map('map-desktop').setView([this.userLocation.lat, this.userLocation.lng], 14);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                minZoom: 10,
                maxZoom: 18
            }).addTo(this.desktopMap);
            
            this.addUserMarker(this.desktopMap);
            this.addVehicleMarkers(this.desktopMap, this.desktopMarkers);
        }
    },
    
    /**
     * Añadir marcador del usuario
     */
    addUserMarker(map) {
        if (!map || !this.userLocation) return;
        
        const userIcon = L.divIcon({
            className: 'user-marker',
            html: `
                <div style="
                    width: 20px;
                    height: 20px;
                    background-color: #1565C0;
                    border: 3px solid white;
                    border-radius: 50%;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                "></div>
            `,
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });
        
        L.marker([this.userLocation.lat, this.userLocation.lng], {
            icon: userIcon,
            zIndexOffset: 1000
        }).addTo(map).bindPopup('<b>La teva ubicació</b>');
    },
    
    /**
     * Añadir marcadores de vehículos
     */
    addVehicleMarkers(map, markersArray) {
        if (!map) return;
        
        this.vehicles.forEach(vehicle => {
            if (!vehicle.location) return;
            
            const color = this.getBatteryColor(vehicle.battery);
            
            const vehicleIcon = L.divIcon({
                className: 'vehicle-marker',
                html: `
                    <div style="position: relative; width: 40px; height: 40px;">
                        <div style="
                            width: 40px;
                            height: 40px;
                            background-color: ${color};
                            border: 3px solid white;
                            border-radius: 50%;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 20px;
                        ">🚗</div>
                        <div style="
                            position: absolute;
                            bottom: -5px;
                            right: -5px;
                            background-color: white;
                            border: 2px solid ${color};
                            border-radius: 50%;
                            width: 24px;
                            height: 24px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 10px;
                            font-weight: bold;
                            color: ${color};
                        ">${vehicle.battery}%</div>
                    </div>
                `,
                iconSize: [40, 40],
                iconAnchor: [20, 20],
                popupAnchor: [0, -20]
            });
            
            const marker = L.marker([vehicle.location.lat, vehicle.location.lng], {
                icon: vehicleIcon
            }).addTo(map);
            
            const distanceText = vehicle.distance !== undefined 
                ? `<p style="margin: 4px 0;"><strong>Distància:</strong> ${vehicle.distance.toFixed(2)} km</p>`
                : '';
            
            marker.bindPopup(`
                <div style="min-width: 200px;">
                    <h3 style="margin: 0 0 8px 0; font-weight: bold; color: #1565C0;">${vehicle.model}</h3>
                    <p style="margin: 4px 0;"><strong>Matrícula:</strong> ${vehicle.license_plate}</p>
                    <p style="margin: 4px 0;"><strong>Bateria:</strong> <span style="color: ${color}; font-weight: bold;">${vehicle.battery}%</span></p>
                    ${distanceText}
                    <button 
                        onclick="VehicleLocator.handleClaimVehicle(${vehicle.id})"
                        style="
                            margin-top: 12px;
                            width: 100%;
                            background-color: #1565C0;
                            color: white;
                            padding: 8px 16px;
                            border: none;
                            border-radius: 8px;
                            font-weight: bold;
                            cursor: pointer;
                        "
                        onmouseover="this.style.opacity='0.9'"
                        onmouseout="this.style.opacity='1'">
                        Reclamar Vehicle
                    </button>
                </div>
            `);
            
            markersArray.push({
                id: vehicle.id,
                marker: marker,
                vehicle: vehicle
            });
        });
    },
    
    /**
     * Obtener color según batería
     */
    getBatteryColor(battery) {
        if (battery >= 80) return '#10B981'; // Verde
        if (battery >= 50) return '#F59E0B'; // Amarillo
        if (battery >= 20) return '#F97316'; // Naranja
        return '#EF4444'; // Rojo
    },
    
    /**
     * Actualizar listas de vehículos
     */
    updateVehicleLists() {
        const normalVehicles = this.vehicles.filter(v => !v.is_accessible);
        const accessibleVehicles = this.vehicles.filter(v => v.is_accessible);
        
        // Listas móviles
        this.renderVehicleList('normal-list', normalVehicles);
        this.renderVehicleList('special-list', accessibleVehicles);
        
        // Listas desktop
        this.renderVehicleList('normal-list-2', normalVehicles);
        this.renderVehicleList('special-list-2', accessibleVehicles);
    },
    
    /**
     * Renderizar lista de vehículos
     */
    renderVehicleList(listId, vehicles) {
        const list = document.getElementById(listId);
        if (!list) return;
        
        if (vehicles.length === 0) {
            list.innerHTML = `
                <li class="bg-gray-100 p-4 rounded-lg shadow-sm text-center text-gray-500">
                    No hi ha vehicles disponibles
                </li>
            `;
            return;
        }
        
        list.innerHTML = vehicles.map(vehicle => `
            <li class="bg-gray-100 p-4 rounded-lg shadow-sm flex items-center justify-between hover:bg-gray-200 transition-colors cursor-pointer"
                onclick="VehicleLocator.focusVehicle(${vehicle.id})">
                <div>
                    <h3 class="font-bold text-base">${vehicle.model || vehicle.license_plate}</h3>
                    <p class="text-gray-700 text-sm">Bateria: ${vehicle.battery}%</p>
                    ${vehicle.distance ? `<p class="text-gray-700 text-xs">📍 ${vehicle.distance.toFixed(2)} km</p>` : ''}
                </div>
                <button 
                    onclick="event.stopPropagation(); VehicleLocator.handleClaimVehicle(${vehicle.id})"
                    class="bg-[#1565C0] text-white px-4 py-2 rounded-lg text-sm hover:bg-[#1151a3] transition-colors duration-300">
                    Reclamar
                </button>
            </li>
        `).join('');
    },
    
    /**
     * Enfocar vehículo en el mapa
     */
    focusVehicle(vehicleId) {
        // Buscar en marcadores móviles
        let item = this.mobileMarkers.find(m => m.id === vehicleId);
        if (item && item.marker && this.mobileMap) {
            this.mobileMap.setView(item.marker.getLatLng(), 16);
            item.marker.openPopup();
            return;
        }
        
        // Buscar en marcadores desktop
        item = this.desktopMarkers.find(m => m.id === vehicleId);
        if (item && item.marker && this.desktopMap) {
            this.desktopMap.setView(item.marker.getLatLng(), 16);
            item.marker.openPopup();
        }
    },
    
    /**
     * Manejar reclamación de vehículo
     */
    handleClaimVehicle(vehicleId) {
        // Buscar vehículo
        const vehicle = this.vehicles.find(v => v.id === vehicleId);
        
        if (!vehicle) {
            console.error('Vehículo no encontrado:', vehicleId);
            return;
        }
        
        // Mostrar modal de confirmación
        if (typeof window.showClaimModal === 'function') {
            window.showClaimModal(vehicle);
        } else {
            // Fallback si el modal no está disponible
            console.warn('Modal de confirmación no disponible, reclamando directamente...');
            Vehicles.claimVehicle(vehicleId);
        }
    },
    
    /**
     * Configurar UI
     */
    setupUI() {
        // Toggle botones para vehículos accesibles
        const toggleButtons = document.querySelectorAll('#toggle-vehicles, #toggle-vehicles-2');
        toggleButtons.forEach(button => {
            button.addEventListener('click', () => {
                const parent = button.closest('.mobile-view') || button.closest('.desktop-view');
                const normalList = parent.querySelector('[id^="normal-list"]');
                const specialList = parent.querySelector('[id^="special-list"]');
                
                if (normalList && specialList) {
                    normalList.classList.toggle('hidden');
                    specialList.classList.toggle('hidden');
                }
            });
        });
        
        // Drawer móvil
        const drawer = document.getElementById('vehicles-drawer');
        const toggleDrawerBtn = document.getElementById('toggle-drawer');
        const closeDrawerBtn = document.getElementById('close-drawer');
        
        if (toggleDrawerBtn && drawer) {
            toggleDrawerBtn.addEventListener('click', () => {
                drawer.classList.remove('translate-x-full');
                drawer.classList.add('translate-x-0');
            });
        }
        
        if (closeDrawerBtn && drawer) {
            closeDrawerBtn.addEventListener('click', () => {
                drawer.classList.remove('translate-x-0');
                drawer.classList.add('translate-x-full');
            });
        }
    }
};

/**
 * Inicializar cuando el DOM esté listo
 */
document.addEventListener('DOMContentLoaded', async () => {
    // Esperar a que Leaflet esté cargado
    if (typeof L === 'undefined') {
        console.error('❌ Leaflet no está cargado');
        return;
    }
    
    await VehicleLocator.init();
});

// Exportar para uso global
window.VehicleLocator = VehicleLocator;
