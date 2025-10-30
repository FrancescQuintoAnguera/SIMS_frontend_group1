/**
 * Modal de confirmación para reclamar vehículos
 * Gestiona la visualización y confirmación del cobro de desbloqueo
 */

const VehicleClaimModal = {
    modal: null,
    currentVehicle: null,
    unlockFee: 0.50, // 50 céntimos
    
    /**
     * Inicializar el modal
     */
    init() {
        this.createModal();
        this.setupEventListeners();
    },
    
    /**
     * Crear el HTML del modal
     */
    createModal() {
        const modalHTML = `
            <div id="claim-modal" class="claim-modal-overlay">
                <div class="claim-modal-container">
                    <div class="claim-modal-header">
                        <h2 class="claim-modal-title">
                            <span>🚗</span>
                            <span>Confirmar reclamació</span>
                        </h2>
                        <button class="claim-modal-close" id="claim-modal-close">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="claim-modal-content">
                        <div class="vehicle-info-card" id="vehicle-info">
                            <!-- Información del vehículo se insertará aquí -->
                        </div>
                        
                        <div class="charge-warning">
                            <div class="charge-warning-icon">⚠️</div>
                            <div class="charge-warning-content">
                                <div class="charge-warning-title">Cost de desbloqueig</div>
                                <div class="charge-warning-text">
                                    Es cobrarà una tarifa de desbloqueig al reclamar aquest vehicle. Aquest càrrec es farà immediatament.
                                </div>
                            </div>
                        </div>
                        
                        <div class="charge-amount">
                            ${this.unlockFee.toFixed(2)}€
                        </div>
                        
                        <p style="text-align: center; color: #6B7280; font-size: 14px; margin-top: 16px;">
                            En confirmar, acceptes els termes i condicions del servei
                        </p>
                    </div>
                    
                    <div class="claim-modal-footer">
                        <button class="claim-modal-button claim-modal-button-cancel" id="claim-modal-cancel">
                            Cancel·lar
                        </button>
                        <button class="claim-modal-button claim-modal-button-confirm" id="claim-modal-confirm">
                            Acceptar i reclamar
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Insertar el modal en el body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('claim-modal');
    },
    
    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        // Cerrar modal al hacer clic en el overlay
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });
        
        // Botón cerrar
        const closeBtn = document.getElementById('claim-modal-close');
        closeBtn.addEventListener('click', () => this.close());
        
        // Botón cancelar
        const cancelBtn = document.getElementById('claim-modal-cancel');
        cancelBtn.addEventListener('click', () => this.close());
        
        // Botón confirmar
        const confirmBtn = document.getElementById('claim-modal-confirm');
        confirmBtn.addEventListener('click', () => this.confirm());
        
        // Cerrar con tecla ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('active')) {
                this.close();
            }
        });
    },
    
    /**
     * Mostrar el modal con la información del vehículo
     */
    show(vehicle) {
        this.currentVehicle = vehicle;
        this.updateVehicleInfo(vehicle);
        this.modal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevenir scroll
    },
    
    /**
     * Actualizar la información del vehículo en el modal
     */
    updateVehicleInfo(vehicle) {
        const vehicleInfoContainer = document.getElementById('vehicle-info');
        
        // Determinar el color de la batería
        const batteryColor = vehicle.battery >= 80 ? '#10B981' : 
                            vehicle.battery >= 50 ? '#F59E0B' : 
                            vehicle.battery >= 20 ? '#F97316' : '#EF4444';
        
        const infoHTML = `
            <div class="vehicle-info-row">
                <span class="vehicle-info-label">Model:</span>
                <span class="vehicle-info-value">${vehicle.model}</span>
            </div>
            <div class="vehicle-info-row">
                <span class="vehicle-info-label">Matrícula:</span>
                <span class="vehicle-info-value">${vehicle.license_plate}</span>
            </div>
            <div class="vehicle-info-row">
                <span class="vehicle-info-label">Bateria:</span>
                <span class="vehicle-info-value" style="color: ${batteryColor};">
                    ${vehicle.battery}% 🔋
                </span>
            </div>
            ${vehicle.distance ? `
                <div class="vehicle-info-row">
                    <span class="vehicle-info-label">Distància:</span>
                    <span class="vehicle-info-value">${vehicle.distance.toFixed(2)} km</span>
                </div>
            ` : ''}
            ${vehicle.is_accessible ? `
                <div class="vehicle-info-row">
                    <span class="vehicle-info-label">Accessible:</span>
                    <span class="vehicle-info-value">✓ Sí</span>
                </div>
            ` : ''}
        `;
        
        vehicleInfoContainer.innerHTML = infoHTML;
    },
    
    /**
     * Cerrar el modal
     */
    close() {
        this.modal.classList.remove('active');
        document.body.style.overflow = ''; // Restaurar scroll
        this.currentVehicle = null;
    },
    
    /**
     * Confirmar reclamación del vehículo
     */
    async confirm() {
        if (!this.currentVehicle) {
            console.error('❌ No hay vehículo seleccionado');
            showToast('Error: No hay vehículo seleccionado', 'error');
            return;
        }
        
        const confirmBtn = document.getElementById('claim-modal-confirm');
        const cancelBtn = document.getElementById('claim-modal-cancel');
        
        // Deshabilitar botones y mostrar loading
        confirmBtn.disabled = true;
        confirmBtn.classList.add('claim-modal-button-loading');
        cancelBtn.disabled = true;
        
        console.log('🚗 Reclamando vehículo:', this.currentVehicle);
        
        try {
            // Llamar a la función de reclamar vehículo
            const result = await Vehicles.claimVehicle(this.currentVehicle.id);
            
            console.log('📊 Resultado de reclamación:', result);
            
            if (result.success) {
                // Cerrar el modal antes de la redirección
                this.close();
                
                console.log('✅ Vehículo reclamado exitosamente, redirigiendo...');
                
                // Mostrar mensaje de éxito
                showToast('✅ Vehicle reclamat amb èxit! Redirigint...', 'success', 2000);
            } else {
                // Si falla, restaurar los botones
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('claim-modal-button-loading');
                cancelBtn.disabled = false;
                
                // Mostrar mensaje de error específico
                const errorMsg = result.message || result.error || 'Error desconegut al reclamar el vehicle';
                console.error('❌ Error al reclamar:', errorMsg);
                
                // Mostrar toast con el error
                showToast(`❌ Error: ${errorMsg}`, 'error');
            }
        } catch (error) {
            console.error('❌ Excepción al confirmar reclamación:', error);
            
            // Restaurar botones en caso de error
            confirmBtn.disabled = false;
            confirmBtn.classList.remove('claim-modal-button-loading');
            cancelBtn.disabled = false;
            
            // Mostrar mensaje de error
            const errorMsg = error.message || 'Error al procesar la reclamació';
            showToast(`❌ Error: ${errorMsg}`, 'error');
        }
    }
};

/**
 * Función global para abrir el modal de confirmación
 * Esta función será llamada desde los botones de reclamar
 */
window.showClaimModal = function(vehicle) {
    VehicleClaimModal.show(vehicle);
};

/**
 * Inicializar el modal cuando el DOM esté listo
 */
document.addEventListener('DOMContentLoaded', () => {
    VehicleClaimModal.init();
});

// Exportar para uso global
window.VehicleClaimModal = VehicleClaimModal;
