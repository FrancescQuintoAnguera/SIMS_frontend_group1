/**
 * JavaScript para la página de administrar vehículo
 * Gestiona la visualización y control del vehículo reclamado
 */

const VehicleControl = {
    currentVehicle: null,
    mapMobile: null,
    mapDesktop: null,
    vehicleMarkerMobile: null,
    vehicleMarkerDesktop: null,
    isEngineOn: false,
    
    /**
     * Inicializar la página
     */
    async init() {
        console.log('🚗 Inicializando control de vehículo...');
        
        // Esperar un momento para asegurar que localStorage esté actualizado
        await new Promise(resolve => setTimeout(resolve, 200));
        
        // Cargar vehículo actual primero
        await this.loadCurrentVehicle();
        
        // Si no hay vehículo, la función loadCurrentVehicle ya habrá redirigido
        if (!this.currentVehicle) {
            return;
        }
        
        // Configurar controles
        this.setupControls();
        
        // Configurar paginación móvil
        this.setupPagination();
        
        // Configurar botón de release
        this.setupReleaseButton();
        
        // Esperar para que el DOM esté completamente listo y los contenedores visibles
        setTimeout(() => {
            // Inicializar mapa
            this.initMap();
        }, 500);
        
        console.log('✅ Control de vehículo inicializado');
    },
    
    /**
     * Cargar el vehículo actual
     */
    async loadCurrentVehicle() {
        try {
            console.log('🔍 Buscando vehículo actual...');
            console.log('👤 User ID de sesión:', window.sessionUserId);
            
            let vehicleFromServer = null;
            let vehicleFromStorage = null;
            
            // PRIMERO: Intentar obtener desde el servidor (fuente de verdad)
            try {
                console.log('📡 Consultando servidor...');
                const response = await fetch('/php/api/vehicles.php?action=current', {
                    method: 'GET',
                    credentials: 'include'
                });
                
                console.log('📡 Response status:', response.status);
                
                if (response.ok) {
                    const data = await response.json();
                    console.log('📡 Respuesta del servidor:', data);
                    
                    if (data.success && data.vehicle) {
                        vehicleFromServer = data.vehicle;
                        console.log('✅ Vehículo encontrado en servidor:', vehicleFromServer);
                        
                        // Guardar en localStorage para uso offline
                        try {
                            localStorage.setItem('currentVehicle', JSON.stringify(data.vehicle));
                            console.log('💾 Guardado en localStorage');
                        } catch (e) {
                            console.warn('⚠️ No se pudo guardar en localStorage:', e);
                        }
                    } else {
                        console.warn('⚠️ Servidor dice: no hay vehículo activo');
                    }
                } else {
                    console.warn('⚠️ Error del servidor:', response.status);
                }
            } catch (serverError) {
                console.warn('⚠️ No se pudo conectar con el servidor:', serverError);
            }
            
            // SEGUNDO: Si el servidor no tiene nada, intentar localStorage (fallback)
            if (!vehicleFromServer) {
                console.log('📦 Intentando cargar desde localStorage...');
                try {
                    const stored = localStorage.getItem('currentVehicle');
                    console.log('📦 localStorage raw:', stored ? stored.substring(0, 100) + '...' : 'null');
                    
                    if (stored && stored !== 'undefined' && stored !== 'null') {
                        vehicleFromStorage = JSON.parse(stored);
                        console.log('✅ Vehículo encontrado en localStorage:', vehicleFromStorage);
                    } else {
                        console.log('⚠️ No hay vehículo válido en localStorage');
                    }
                } catch (storageError) {
                    console.warn('⚠️ Error al leer localStorage:', storageError);
                    localStorage.removeItem('currentVehicle');
                }
            }
            
            // Usar el vehículo que encontramos (servidor tiene prioridad)
            this.currentVehicle = vehicleFromServer || vehicleFromStorage;
            
            if (!this.currentVehicle) {
                console.error('❌ No hay vehículo reclamado');
                console.log('🔍 Verificando estado en servidor...');
                
                // Llamar al endpoint de debug
                try {
                    const debugResponse = await fetch('/php/api/debug-vehicle.php', {
                        credentials: 'include'
                    });
                    if (debugResponse.ok) {
                        const debugData = await debugResponse.json();
                        console.log('🐛 Debug info:', debugData);
                    }
                } catch (e) {
                    console.log('⚠️ No se pudo obtener debug info');
                }
                
                showToast('No tens cap vehicle reclamat. Redirigint...', 'warning', 2000);
                setTimeout(() => {
                    window.location.href = './localitzar-vehicle.html';
                }, 2000);
                return;
            }
            
            console.log('✅ Vehículo cargado:', this.currentVehicle);
            
            // Actualizar UI
            this.updateVehicleInfo();
            
        } catch (error) {
            console.error('❌ Error al cargar vehículo:', error);
            showToast('Error al carregar el vehicle. Si us plau, torna-ho a intentar.', 'error', 2000);
            setTimeout(() => {
                window.location.href = './localitzar-vehicle.html';
            }, 2000);
        }
    },
    
    /**
     * Actualizar información del vehículo en la UI
     */
    updateVehicleInfo() {
        if (!this.currentVehicle) {
            console.warn('⚠️ No hay vehículo para actualizar');
            return;
        }
        
        console.log('🔄 Actualizando información del vehículo en UI...');
        console.log('📊 Datos del vehículo:', this.currentVehicle);
        
        // Actualizar matrícula
        const licensePlateElements = document.querySelectorAll('[data-vehicle-license]');
        const licensePlate = this.currentVehicle.license_plate || this.currentVehicle.plate || 'N/A';
        licensePlateElements.forEach(el => {
            el.textContent = licensePlate;
        });
        console.log(`✓ Matrícula actualizada: ${licensePlate}`);
        
        // Actualizar modelo (marca + modelo)
        const modelElements = document.querySelectorAll('[data-vehicle-model]');
        let fullModel = 'N/A';
        
        if (this.currentVehicle.brand && this.currentVehicle.model) {
            fullModel = `${this.currentVehicle.brand} ${this.currentVehicle.model}`;
        } else if (this.currentVehicle.model) {
            fullModel = this.currentVehicle.model;
        }
        
        modelElements.forEach(el => {
            el.textContent = fullModel;
        });
        console.log(`✓ Modelo actualizado: ${fullModel}`);
        
        // Actualizar batería
        const battery = this.currentVehicle.battery || this.currentVehicle.battery_level || 85;
        console.log(`🔋 Nivel de batería: ${battery}%`);
        this.updateBattery(battery);
        console.log(`✓ Batería actualizada: ${battery}%`);
        
        // Actualizar estado
        const statusElements = document.querySelectorAll('[data-vehicle-status]');
        const statusText = this.isEngineOn ? 'En marxa' : 'Operatiu';
        statusElements.forEach(el => {
            el.textContent = statusText;
        });
        console.log(`✓ Estado actualizado: ${statusText}`);
        
        console.log('✅ UI actualizada correctamente');
    },
    
    /**
     * Actualizar batería
     */
    updateBattery(percentage) {
        const batteryBars = document.querySelectorAll('[data-battery-bar]');
        const batteryTexts = document.querySelectorAll('[data-battery-text]');
        
        // Determinar color según porcentaje
        let color = '#00C853'; // Verde
        if (percentage < 20) {
            color = '#EF4444'; // Rojo
        } else if (percentage < 50) {
            color = '#F97316'; // Naranja
        } else if (percentage < 80) {
            color = '#F59E0B'; // Amarillo
        }
        
        batteryBars.forEach(bar => {
            bar.style.width = `${percentage}%`;
            bar.style.backgroundColor = color;
        });
        
        batteryTexts.forEach(text => {
            text.textContent = `${percentage}%`;
        });
    },
    
    /**
     * Configurar controles del vehículo
     */
    setupControls() {
        // Botón Engegar/Apagar
        const engineButtons = document.querySelectorAll('[data-control="engine"]');
        engineButtons.forEach(btn => {
            btn.addEventListener('click', () => this.toggleEngine());
        });
        
        // Botón Claxon
        const hornButtons = document.querySelectorAll('[data-control="horn"]');
        hornButtons.forEach(btn => {
            btn.addEventListener('click', () => this.activateHorn());
        });
        
        // Botón Luces
        const lightsButtons = document.querySelectorAll('[data-control="lights"]');
        lightsButtons.forEach(btn => {
            btn.addEventListener('click', () => this.activateLights());
        });
        
        // Botón Puertas
        const doorsButtons = document.querySelectorAll('[data-control="doors"]');
        doorsButtons.forEach(btn => {
            btn.addEventListener('click', () => this.toggleDoors());
        });
    },
    
    /**
     * Encender/Apagar motor
     */
    async toggleEngine() {
        try {
            console.log('🔧 Cambiando estado del motor...');
            
            if (this.isEngineOn) {
                const result = await Vehicles.stopEngine();
                if (result && result.success !== false) {
                    this.isEngineOn = false;
                    this.updateEngineButton();
                    console.log('✅ Motor apagado');
                }
            } else {
                const result = await Vehicles.startEngine();
                if (result && result.success !== false) {
                    this.isEngineOn = true;
                    this.updateEngineButton();
                    console.log('✅ Motor encendido');
                }
            }
        } catch (error) {
            console.error('❌ Error al controlar motor:', error);
            showToast('Error al controlar el motor', 'error');
        }
    },
    
    /**
     * Actualizar botón de motor
     */
    updateEngineButton() {
        const engineButtons = document.querySelectorAll('[data-control="engine"]');
        engineButtons.forEach(btn => {
            const front = btn.querySelector('.front');
            if (!front) return;
            
            const textSpans = front.querySelectorAll('span:not(.shadow):not(.edge)');
            
            if (this.isEngineOn) {
                btn.classList.remove('yellow');
                btn.classList.add('red');
                
                // Actualizar texto
                const img = front.querySelector('img');
                front.innerHTML = '';
                if (img) front.appendChild(img);
                const span1 = document.createElement('span');
                span1.textContent = 'Apagar';
                const span2 = document.createElement('span');
                span2.textContent = 'Motor';
                front.appendChild(span1);
                front.appendChild(span2);
            } else {
                btn.classList.remove('red');
                btn.classList.add('yellow');
                
                // Actualizar texto
                const img = front.querySelector('img');
                front.innerHTML = '';
                if (img) front.appendChild(img);
                const span1 = document.createElement('span');
                span1.textContent = 'Engegar';
                const span2 = document.createElement('span');
                span2.textContent = '/Apagar';
                front.appendChild(span1);
                front.appendChild(span2);
            }
        });
        
        // Actualizar estado
        const statusElements = document.querySelectorAll('[data-vehicle-status]');
        const statusText = this.isEngineOn ? 'En marxa' : 'Operatiu';
        statusElements.forEach(el => {
            el.textContent = statusText;
        });
        
        // Actualizar color del botón de estado
        const stateButtons = document.querySelectorAll('.pushable.green');
        stateButtons.forEach(btn => {
            if (this.isEngineOn) {
                btn.classList.remove('green');
                btn.classList.add('red');
            } else {
                btn.classList.remove('red');
                btn.classList.add('green');
            }
        });
    },
    
    /**
     * Activar claxon
     */
    async activateHorn() {
        try {
            console.log('📢 Activando claxon...');
            const result = await Vehicles.activateHorn();
            if (result && result.success !== false) {
                console.log('✅ Claxon activado');
                // Feedback visual
                const hornButtons = document.querySelectorAll('[data-control="horn"]');
                hornButtons.forEach(btn => {
                    btn.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        btn.style.transform = '';
                    }, 200);
                });
            }
        } catch (error) {
            console.error('❌ Error al activar claxon:', error);
        }
    },
    
    /**
     * Activar luces
     */
    async activateLights() {
        try {
            console.log('💡 Activando luces...');
            const result = await Vehicles.activateLights();
            if (result && result.success !== false) {
                console.log('✅ Luces activadas');
                // Feedback visual
                const lightsButtons = document.querySelectorAll('[data-control="lights"]');
                lightsButtons.forEach(btn => {
                    btn.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        btn.style.transform = '';
                    }, 200);
                });
            }
        } catch (error) {
            console.error('❌ Error al activar luces:', error);
        }
    },
    
    /**
     * Bloquear/Desbloquear puertas
     */
    async toggleDoors() {
        try {
            console.log('🔒 Cambiando estado de puertas...');
            const result = await Vehicles.toggleDoors(true);
            if (result && result.success !== false) {
                console.log('✅ Puertas bloqueadas/desbloqueadas');
                // Feedback visual
                const doorsButtons = document.querySelectorAll('[data-control="doors"]');
                doorsButtons.forEach(btn => {
                    btn.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        btn.style.transform = '';
                    }, 200);
                });
            }
        } catch (error) {
            console.error('❌ Error al controlar puertas:', error);
        }
    },
    
    /**
     * Inicializar mapa
     */
    async initMap() {
        // Esperar a que Leaflet esté disponible
        if (typeof L === 'undefined') {
            console.warn('⚠️ Leaflet no está cargado, esperando...');
            setTimeout(() => this.initMap(), 500);
            return;
        }
        
        // Inicializar mapa móvil
        const mapContainerMobile = document.getElementById('vehicle-map-mobile');
        const mapContainerDesktop = document.getElementById('vehicle-map-desktop');
        
        if (!mapContainerMobile && !mapContainerDesktop) {
            console.warn('⚠️ Contenedores de mapa no encontrados');
            return;
        }
        
        try {
            // Obtener ubicación del vehículo
            let lat = 40.7117;
            let lng = 0.5783;
            
            // Verificar diferentes formatos de coordenadas
            if (this.currentVehicle) {
                if (this.currentVehicle.location) {
                    lat = parseFloat(this.currentVehicle.location.lat);
                    lng = parseFloat(this.currentVehicle.location.lng);
                } else if (this.currentVehicle.latitude && this.currentVehicle.longitude) {
                    lat = parseFloat(this.currentVehicle.latitude);
                    lng = parseFloat(this.currentVehicle.longitude);
                } else if (this.currentVehicle.lat && this.currentVehicle.lng) {
                    lat = parseFloat(this.currentVehicle.lat);
                    lng = parseFloat(this.currentVehicle.lng);
                }
            }
            
            console.log('🗺️ Inicializando mapas en ubicación:', { lat, lng });
            
            if (isNaN(lat) || isNaN(lng)) {
                console.error('❌ Coordenadas inválidas, usando por defecto');
                lat = 40.7117;
                lng = 0.5783;
            }
            
            // Obtener color según batería
            const battery = this.currentVehicle?.battery || this.currentVehicle?.battery_level || 85;
            const color = this.getBatteryColor(battery);
            
            // Crear icono del vehículo
            const vehicleIcon = L.divIcon({
                className: 'vehicle-marker',
                html: `
                    <div style="
                        width: 50px;
                        height: 50px;
                        background-color: ${color};
                        border: 4px solid white;
                        border-radius: 50%;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.4);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 24px;
                    ">🚗</div>
                `,
                iconSize: [50, 50],
                iconAnchor: [25, 25]
            });
            
            // Crear texto del popup
            const licensePlate = this.currentVehicle.license_plate || this.currentVehicle.plate || 'N/A';
            let modelText = 'Vehicle';
            
            if (this.currentVehicle.brand && this.currentVehicle.model) {
                modelText = `${this.currentVehicle.brand} ${this.currentVehicle.model}`;
            } else if (this.currentVehicle.model) {
                modelText = this.currentVehicle.model;
            }
            
            const popupContent = `
                <div style="text-align: center; padding: 5px;">
                    <b style="font-size: 14px;">${modelText}</b><br>
                    <span style="color: #666; font-size: 12px;">${licensePlate}</span><br>
                    <span style="color: ${color}; font-weight: bold; font-size: 13px;">🔋 ${battery}%</span>
                </div>
            `;
            
            // Inicializar mapa móvil
            if (mapContainerMobile && !this.mapMobile) {
                console.log('🗺️ Inicializando mapa móvil...');
                
                // Asegurar que el contenedor tenga dimensiones
                mapContainerMobile.style.width = '100%';
                mapContainerMobile.style.height = '100%';
                
                this.mapMobile = L.map('vehicle-map-mobile', {
                    center: [lat, lng],
                    zoom: 16,
                    scrollWheelZoom: true,
                    zoomControl: true
                });
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    minZoom: 10,
                    maxZoom: 18
                }).addTo(this.mapMobile);
                
                this.vehicleMarkerMobile = L.marker([lat, lng], {
                    icon: vehicleIcon
                }).addTo(this.mapMobile);
                
                this.vehicleMarkerMobile.bindPopup(popupContent).openPopup();
                
                setTimeout(() => {
                    if (this.mapMobile) {
                        this.mapMobile.invalidateSize();
                    }
                }, 300);
                
                console.log('✅ Mapa móvil inicializado');
            }
            
            // Inicializar mapa escritorio
            if (mapContainerDesktop && !this.mapDesktop) {
                console.log('🗺️ Inicializando mapa escritorio...');
                
                // Asegurar que el contenedor tenga dimensiones
                mapContainerDesktop.style.width = '100%';
                mapContainerDesktop.style.height = '100%';
                
                this.mapDesktop = L.map('vehicle-map-desktop', {
                    center: [lat, lng],
                    zoom: 16,
                    scrollWheelZoom: true,
                    zoomControl: true
                });
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    minZoom: 10,
                    maxZoom: 18
                }).addTo(this.mapDesktop);
                
                this.vehicleMarkerDesktop = L.marker([lat, lng], {
                    icon: vehicleIcon
                }).addTo(this.mapDesktop);
                
                this.vehicleMarkerDesktop.bindPopup(popupContent).openPopup();
                
                // Dar tiempo para que el contenedor sea visible
                setTimeout(() => {
                    if (this.mapDesktop) {
                        this.mapDesktop.invalidateSize();
                        console.log('🔄 Mapa desktop actualizado');
                    }
                }, 300);
                
                console.log('✅ Mapa escritorio inicializado');
            }
            
            console.log('✅ Mapas inicializados correctamente');
            
            // Configurar listener de resize para ambos mapas
            let resizeTimeout;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    if (this.mapMobile) {
                        this.mapMobile.invalidateSize();
                        console.log('🔄 Mapa móvil redimensionado');
                    }
                    if (this.mapDesktop) {
                        this.mapDesktop.invalidateSize();
                        console.log('🔄 Mapa desktop redimensionado');
                    }
                }, 200);
            });
            
            // Observer para detectar cuando los contenedores son visibles
            if (mapContainerDesktop) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && this.mapDesktop) {
                            setTimeout(() => {
                                this.mapDesktop.invalidateSize();
                                console.log('🔄 Mapa desktop visible - actualizado');
                            }, 100);
                        }
                    });
                }, { threshold: 0.1 });
                
                observer.observe(mapContainerDesktop);
            }
            
            if (mapContainerMobile) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && this.mapMobile) {
                            setTimeout(() => {
                                this.mapMobile.invalidateSize();
                                console.log('🔄 Mapa móvil visible - actualizado');
                            }, 100);
                        }
                    });
                }, { threshold: 0.1 });
                
                observer.observe(mapContainerMobile);
            }
            
        } catch (error) {
            console.error('❌ Error al inicializar mapas:', error);
        }
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
     * Configurar paginación móvil
     */
    setupPagination() {
        const mainScroll = document.querySelector('main');
        const dots = document.querySelectorAll('.dot');
        
        if (!mainScroll || !dots.length) return;
        
        function updateDots() {
            const scrollLeft = mainScroll.scrollLeft;
            const width = mainScroll.clientWidth;
            const index = Math.round(scrollLeft / width);
            
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
        }
        
        mainScroll.addEventListener('scroll', updateDots);
        dots[0].classList.add('active');
    },
    
    /**
     * Configurar botón de release
     */
    setupReleaseButton() {
        console.log('🔧 Configurando botón de release...');
        
        const releaseBtnMobile = document.getElementById('release-vehicle-btn-mobile');
        const releaseBtnDesktop = document.getElementById('release-vehicle-btn-desktop');
        const modal = document.getElementById('release-modal');
        const cancelBtn = document.getElementById('release-cancel-btn');
        const confirmBtn = document.getElementById('release-confirm-btn');
        
        if (!modal || !cancelBtn || !confirmBtn) {
            console.error('❌ Elementos del modal no encontrados');
            return;
        }
        
        // Función para abrir el modal
        const openModal = () => {
            console.log('📂 Abriendo modal de release...');
            
            if (!this.currentVehicle) {
                console.error('❌ No hay vehículo actual');
                return;
            }
            
            // Actualizar información del modal
            const licensePlate = this.currentVehicle.license_plate || this.currentVehicle.plate || 'N/A';
            const brand = this.currentVehicle.brand || '';
            const model = this.currentVehicle.model || '';
            const vehicleInfo = brand && model ? `${brand} ${model} (${licensePlate})` : licensePlate;
            
            document.getElementById('release-vehicle-info').textContent = vehicleInfo;
            
            // Calcular tiempo de uso (ejemplo simple)
            const startTime = new Date(this.currentVehicle.booking_start || Date.now());
            const now = new Date();
            const diffMs = now - startTime;
            const diffMins = Math.floor(diffMs / 60000);
            const hours = Math.floor(diffMins / 60);
            const mins = diffMins % 60;
            
            let timeText = '';
            if (hours > 0) {
                timeText = `${hours}h ${mins}min`;
            } else {
                timeText = `${mins} minuts`;
            }
            
            document.getElementById('release-time-info').textContent = timeText;
            
            // Calcular costo estimado
            const pricePerMin = parseFloat(this.currentVehicle.price_per_minute || 0.38);
            const estimatedCost = (diffMins * pricePerMin).toFixed(2);
            
            document.getElementById('release-cost-info').textContent = `${estimatedCost}€`;
            
            // Mostrar modal
            modal.classList.remove('hidden');
        };
        
        // Función para cerrar el modal
        const closeModal = () => {
            console.log('📁 Cerrando modal de release...');
            modal.classList.add('hidden');
        };
        
        // Función para confirmar release
        const confirmRelease = async () => {
            console.log('✅ Confirmando release del vehículo...');
            
            try {
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Finalitzant...';
                
                const result = await Vehicles.releaseVehicle();
                
                if (result.success) {
                    console.log('✅ Vehículo liberado exitosamente');
                    
                    // Limpiar localStorage
                    localStorage.removeItem('currentVehicle');
                    
                    // Cerrar modal
                    closeModal();
                    
                    // Mostrar mensaje de éxito
                    showToast('Reserva finalitzada correctament!', 'success', 2000);
                    
                    // Redirigir a localitzar vehículos
                    setTimeout(() => {
                        window.location.href = './localitzar-vehicle.html';
                    }, 2000);
                } else {
                    console.error('❌ Error al liberar vehículo:', result.message);
                    showToast('Error al finalitzar la reserva: ' + result.message, 'error');
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Finalitzar';
                }
            } catch (error) {
                console.error('❌ Excepción al liberar vehículo:', error);
                showToast('Error al finalitzar la reserva. Si us plau, intenta-ho de nou.', 'error');
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Finalitzar';
            }
        };
        
        // Event listeners
        if (releaseBtnMobile) {
            releaseBtnMobile.addEventListener('click', openModal);
            console.log('✅ Botón mobile configurado');
        }
        
        if (releaseBtnDesktop) {
            releaseBtnDesktop.addEventListener('click', openModal);
            console.log('✅ Botón desktop configurado');
        }
        
        cancelBtn.addEventListener('click', closeModal);
        confirmBtn.addEventListener('click', confirmRelease);
        
        // Cerrar modal al hacer clic fuera
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
        
        console.log('✅ Botón de release configurado correctamente');
    }
};

/**
 * Inicializar cuando el DOM esté listo
 */
document.addEventListener('DOMContentLoaded', () => {
    VehicleControl.init();
});

// Exportar para uso global
window.VehicleControl = VehicleControl;
