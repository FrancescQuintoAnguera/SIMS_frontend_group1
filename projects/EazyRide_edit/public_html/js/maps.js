/* global Vehicles, Utils */
 
/**
 * Maps Module for VoltiaCar Application
 * Handles interactive maps with vehicle locations using Leaflet.js and OpenStreetMap
 * Note: L is the Leaflet.js global variable
 */

const Maps = {
    map: null,
    markers: [],
    userMarker: null,
    userLocation: null,
    
    /**
     * Initialize map
     */
    async initMap(containerId = 'map', options = {}) {
        const defaultOptions = {
            center: [41.3851, 2.1734], // Barcelona coordinates
            zoom: 14,
            minZoom: 10,
            maxZoom: 18
        };
        
        const config = { ...defaultOptions, ...options };
        
        // Create map
        this.map = L.map(containerId).setView(config.center, config.zoom);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            minZoom: config.minZoom,
            maxZoom: config.maxZoom
        }).addTo(this.map);
        
        // Get user location
        await this.getUserLocation();
        
        // Load and display vehicles
        await this.loadVehicles();
        
        return this.map;
    },
    
    /**
     * Get user's current location
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
                        
                        // Add user marker
                        this.addUserMarker(this.userLocation);
                        
                        // Center map on user
                        if (this.map) {
                            this.map.setView([this.userLocation.lat, this.userLocation.lng], 15);
                        }
                        
                        resolve(this.userLocation);
                    },
                    (error) => {
                        console.warn('Geolocation error:', error);
                        
                        // Show user-friendly error message
                        let errorMessage = '';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = 'Permisos de geolocalització denegats. Utilitzant ubicació per defecte (Barcelona).';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = 'Ubicació no disponible. Utilitzant ubicació per defecte (Barcelona).';
                                break;
                            case error.TIMEOUT:
                                errorMessage = 'Temps d\'espera esgotat. Utilitzant ubicació per defecte (Barcelona).';
                                break;
                            default:
                                errorMessage = 'Error de geolocalització. Utilitzant ubicació per defecte (Barcelona).';
                        }
                        
                        // Show notification if Utils is available
                        if (typeof Utils !== 'undefined' && Utils.showToast) {
                            Utils.showToast(errorMessage, 'warning');
                        } else {
                            console.warn(errorMessage);
                        }
                        
                        // Use default location (Barcelona)
                        this.userLocation = { lat: 41.3851, lng: 2.1734 };
                        resolve(this.userLocation);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    }
                );
            } else {
                // Geolocation not supported
                console.warn('Geolocation not supported by this browser');
                if (typeof Utils !== 'undefined' && Utils.showToast) {
                    Utils.showToast('Geolocalització no suportada. Utilitzant ubicació per defecte.', 'info');
                }
                this.userLocation = { lat: 41.3851, lng: 2.1734 };
                resolve(this.userLocation);
            }
        });
    },
    
    /**
     * Add user location marker
     */
    addUserMarker(location) {
        if (!this.map) return;
        
        // Create custom user icon
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
        
        this.userMarker = L.marker([location.lat, location.lng], {
            icon: userIcon,
            zIndexOffset: 1000
        }).addTo(this.map);
        
        this.userMarker.bindPopup('<b>La teva ubicació</b>');
    },
    
    /**
     * Load and display vehicles on map
     */
    async loadVehicles(filters = {}) {
        try {
            let vehicles;
            
            // If user location is available, fetch nearby vehicles with distance
            if (this.userLocation) {
                vehicles = await this.getNearbyVehicles(
                    this.userLocation.lat,
                    this.userLocation.lng,
                    filters.maxDistance || 10
                );
            } else {
                vehicles = await Vehicles.getAvailableVehicles();
            }
            
            // Apply additional filters
            if (filters.vehicleType) {
                vehicles = vehicles.filter(v => v.type === filters.vehicleType);
            }
            
            if (filters.minBattery) {
                vehicles = vehicles.filter(v => v.battery >= filters.minBattery);
            }
            
            if (filters.accessibleOnly) {
                vehicles = vehicles.filter(v => v.is_accessible);
            }
            
            // Sort by distance if available
            if (this.userLocation) {
                vehicles = this.sortVehiclesByDistance(vehicles);
            }
            
            this.displayVehicles(vehicles);
            return vehicles;
        } catch (error) {
            console.error('Error loading vehicles:', error);
            return [];
        }
    },
    
    /**
     * Get nearby vehicles from API
     */
    async getNearbyVehicles(lat, lng, radius = 10) {
        try {
            // Determine the correct path based on current location
            const basePath = window.location.pathname.includes('/pages/') 
                ? '../../php/api/vehicles.php' 
                : '/php/api/vehicles.php';
            
            const response = await fetch(
                `${basePath}?action=nearby&lat=${lat}&lng=${lng}&radius=${radius}`,
                {
                    method: 'GET',
                    credentials: 'include'
                }
            );
            
            if (response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (data.success && data.vehicles) {
                        return data.vehicles;
                    }
                }
            }
            
            // Fallback to all vehicles
            return await Vehicles.getAvailableVehicles();
        } catch (error) {
            console.error('Error fetching nearby vehicles:', error);
            return await Vehicles.getAvailableVehicles();
        }
    },
    
    /**
     * Sort vehicles by distance from user
     */
    sortVehiclesByDistance(vehicles) {
        if (!this.userLocation) return vehicles;
        
        return vehicles.sort((a, b) => {
            const distA = a.distance !== undefined ? a.distance : 
                         Vehicles.calculateDistance(this.userLocation, a.location);
            const distB = b.distance !== undefined ? b.distance : 
                         Vehicles.calculateDistance(this.userLocation, b.location);
            return distA - distB;
        });
    },
    
    /**
     * Display vehicles on map
     */
    displayVehicles(vehicles) {
        if (!this.map) return;
        
        // Clear existing markers
        this.clearVehicleMarkers();
        
        // Add marker for each vehicle
        vehicles.forEach(vehicle => {
            this.addVehicleMarker(vehicle);
        });
    },
    
    /**
     * Add vehicle marker to map
     */
    addVehicleMarker(vehicle) {
        if (!this.map || !vehicle.location) return;
        
        // Get battery color
        const color = this.getBatteryColor(vehicle.battery);
        
        // Create custom vehicle icon
        const vehicleIcon = L.divIcon({
            className: 'vehicle-marker',
            html: `
                <div style="
                    position: relative;
                    width: 40px;
                    height: 40px;
                ">
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
        
        // Create marker
        const marker = L.marker([vehicle.location.lat, vehicle.location.lng], {
            icon: vehicleIcon
        }).addTo(this.map);
        
        // Calculate distance from user
        let distanceText = '';
        if (this.userLocation) {
            const distance = Vehicles.calculateDistance(this.userLocation, vehicle.location);
            distanceText = `<br><small>📍 ${distance.toFixed(2)} km de distància</small>`;
        }
        
        // Features text
        let featuresText = '';
        if (vehicle.features && vehicle.features.length > 0) {
            featuresText = `<br><small>✓ ${vehicle.features.join(', ')}</small>`;
        }
        
        // Create popup content
        const popupContent = `
            <div style="min-width: 200px;">
                <h3 style="margin: 0 0 8px 0; font-weight: bold; color: #1565C0;">
                    ${vehicle.model}
                </h3>
                <p style="margin: 4px 0;">
                    <strong>Matrícula:</strong> ${vehicle.license_plate}
                </p>
                <p style="margin: 4px 0;">
                    <strong>Bateria:</strong> 
                    <span style="color: ${color}; font-weight: bold;">
                        ${vehicle.battery}%
                    </span>
                </p>
                ${distanceText}
                ${featuresText}
                <button 
                    onclick="Maps.claimVehicleFromMap(${vehicle.id})"
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
                    onmouseout="this.style.opacity='1'"
                >
                    Reclamar Vehicle
                </button>
            </div>
        `;
        
        marker.bindPopup(popupContent);
        
        // Store marker reference
        this.markers.push({
            id: vehicle.id,
            marker: marker,
            vehicle: vehicle
        });
        
        return marker;
    },
    
    /**
     * Get battery color based on level
     */
    getBatteryColor(battery) {
        if (battery >= 80) return '#10B981'; // Green
        if (battery >= 50) return '#F59E0B'; // Yellow
        if (battery >= 20) return '#F97316'; // Orange
        return '#EF4444'; // Red
    },
    
    /**
     * Clear all vehicle markers
     */
    clearVehicleMarkers() {
        this.markers.forEach(item => {
            if (item.marker) {
                this.map.removeLayer(item.marker);
            }
        });
        this.markers = [];
    },
    
    /**
     * Claim vehicle from map popup
     */
    async claimVehicleFromMap(vehicleId) {
        const item = this.markers.find(m => m.id === vehicleId);
        if (item && item.vehicle) {
            // Mostrar modal de confirmación
            if (typeof window.showClaimModal === 'function') {
                window.showClaimModal(item.vehicle);
            } else {
                // Fallback si el modal no está disponible
                const result = await Vehicles.claimVehicle(vehicleId);
                if (result.success) {
                    // Redirect handled by Vehicles.claimVehicle
                }
            }
        }
    },
    
    /**
     * Focus on specific vehicle
     */
    focusVehicle(vehicleId) {
        const item = this.markers.find(m => m.id === vehicleId);
        if (item && item.marker) {
            this.map.setView(item.marker.getLatLng(), 16);
            item.marker.openPopup();
        }
    },
    
    /**
     * Filter vehicles on map
     */
    async filterVehicles(filters) {
        // Reload vehicles with filters
        const vehicles = await this.loadVehicles(filters);
        
        // Update vehicle lists if they exist
        this.updateVehicleLists(vehicles);
        
        return vehicles;
    },
    
    /**
     * Update vehicle lists in the UI
     */
    updateVehicleLists(vehicles) {
        // Update mobile list
        const mobileList = document.getElementById('normal-list');
        if (mobileList) {
            this.renderVehicleList(mobileList, vehicles.filter(v => !v.is_accessible));
        }
        
        const mobileAccessibleList = document.getElementById('special-list');
        if (mobileAccessibleList) {
            this.renderVehicleList(mobileAccessibleList, vehicles.filter(v => v.is_accessible));
        }
        
        // Update desktop list
        const desktopList = document.getElementById('normal-list-2');
        if (desktopList) {
            this.renderVehicleList(desktopList, vehicles.filter(v => !v.is_accessible));
        }
        
        const desktopAccessibleList = document.getElementById('special-list-2');
        if (desktopAccessibleList) {
            this.renderVehicleList(desktopAccessibleList, vehicles.filter(v => v.is_accessible));
        }
    },
    
    /**
     * Render vehicle list HTML
     */
    renderVehicleList(listElement, vehicles) {
        if (!listElement) return;
        
        if (vehicles.length === 0) {
            listElement.innerHTML = `
                <li class="bg-gray-100 p-4 rounded-lg shadow-sm text-center text-gray-500">
                    No hi ha vehicles disponibles
                </li>
            `;
            return;
        }
        
        listElement.innerHTML = vehicles.map(vehicle => {
            const distanceText = vehicle.distance !== undefined 
                ? `<p class="text-gray-700 text-xs">📍 ${vehicle.distance.toFixed(2)} km</p>`
                : '';
            
            const accessibilityText = vehicle.is_accessible && vehicle.accessibility_features 
                ? `<p class="text-gray-700 text-sm font-semibold">Adaptat per: ${vehicle.accessibility_features.join(', ')}</p>`
                : '';
            
            const vehicleDataJson = JSON.stringify(vehicle).replace(/"/g, '&quot;');
            
            return `
                <li class="bg-gray-100 p-4 rounded-lg shadow-sm flex items-center justify-between hover:bg-gray-200 transition-colors cursor-pointer"
                    onclick="Maps.focusVehicle(${vehicle.id})">
                    <div>
                        <h3 class="font-bold text-base">${vehicle.model || vehicle.license_plate}</h3>
                        <p class="text-gray-700 text-sm">Bateria: ${vehicle.battery}%</p>
                        ${accessibilityText}
                        ${distanceText}
                    </div>
                    <button 
                        onclick="event.stopPropagation(); Maps.handleClaimFromList(${vehicle.id})"
                        class="bg-[#1565C0] text-white px-4 py-2 rounded-lg text-sm hover:bg-[#1151a3] transition-colors duration-300">
                        Reclamar
                    </button>
                </li>
            `;
        }).join('');
    },
    
    /**
     * Handle claim vehicle from list
     */
    async handleClaimFromList(vehicleId) {
        const item = this.markers.find(m => m.id === vehicleId);
        if (item && item.vehicle) {
            // Mostrar modal de confirmación
            if (typeof window.showClaimModal === 'function') {
                window.showClaimModal(item.vehicle);
            } else {
                // Fallback si el modal no está disponible
                await Vehicles.claimVehicle(vehicleId);
            }
        } else {
            // Si no está en los markers, obtener la info del vehículo
            const vehicle = await Vehicles.getVehicleDetails(vehicleId);
            if (vehicle && typeof window.showClaimModal === 'function') {
                window.showClaimModal(vehicle);
            } else {
                await Vehicles.claimVehicle(vehicleId);
            }
        }
    },
    
    /**
     * Add route between two points
     */
    addRoute(start, end) {
        if (!this.map) return;
        
        // Simple straight line (for production, use routing API)
        const route = L.polyline([
            [start.lat, start.lng],
            [end.lat, end.lng]
        ], {
            color: '#1565C0',
            weight: 4,
            opacity: 0.7,
            dashArray: '10, 10'
        }).addTo(this.map);
        
        // Fit map to show entire route
        this.map.fitBounds(route.getBounds(), { padding: [50, 50] });
        
        return route;
    },
    
    /**
     * Destroy map instance
     */
    destroy() {
        if (this.map) {
            this.map.remove();
            this.map = null;
            this.markers = [];
            this.userMarker = null;
        }
    }
};

/**
 * Initialize map when page loads
 */
document.addEventListener('DOMContentLoaded', () => {
    const mapContainer = document.getElementById('map');
    
    if (mapContainer) {
        // Add Leaflet CSS if not already included
        if (!document.querySelector('link[href*="leaflet"]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            link.integrity = 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=';
            link.crossOrigin = '';
            document.head.appendChild(link);
        }
        
        // Add Leaflet JS if not already included
        if (typeof L === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
            script.crossOrigin = '';
            script.onload = async () => {
                await Maps.initMap('map');
                // Update vehicle lists after map initialization
                const vehicles = await Maps.loadVehicles();
                Maps.updateVehicleLists(vehicles);
            };
            document.head.appendChild(script);
        } else {
            (async () => {
                await Maps.initMap('map');
                // Update vehicle lists after map initialization
                const vehicles = await Maps.loadVehicles();
                Maps.updateVehicleLists(vehicles);
            })();
        }
    }
});

// Export for use in other scripts
window.Maps = Maps;
