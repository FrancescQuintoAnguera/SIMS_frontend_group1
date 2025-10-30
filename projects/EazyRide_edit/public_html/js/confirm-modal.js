/**
 * Sistema de confirmación modal para reemplazar window.confirm()
 */
const ConfirmModal = {
    modal: null,
    
    /**
     * Crear el modal de confirmación
     */
    createModal() {
        if (this.modal) return;
        
        const modalHTML = `
            <div id="confirm-modal" class="fixed inset-0 z-[9999] hidden">
                <!-- Overlay -->
                <div class="absolute inset-0 bg-black bg-opacity-50 transition-opacity" id="confirm-overlay"></div>
                
                <!-- Modal Container -->
                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all scale-95 opacity-0" id="confirm-content">
                        <!-- Header -->
                        <div class="px-6 pt-6 pb-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-900" id="confirm-title">Confirmació</h3>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Body -->
                        <div class="px-6 pb-6">
                            <p class="text-gray-700 leading-relaxed" id="confirm-message">
                                Estàs segur que vols continuar?
                            </p>
                        </div>
                        
                        <!-- Footer -->
                        <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex items-center justify-end space-x-3">
                            <button type="button" id="confirm-cancel-btn" 
                                class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                                Cancel·lar
                            </button>
                            <button type="button" id="confirm-ok-btn" 
                                class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                Acceptar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const div = document.createElement('div');
        div.innerHTML = modalHTML;
        document.body.appendChild(div.firstElementChild);
        this.modal = document.getElementById('confirm-modal');
    },
    
    /**
     * Mostrar el modal de confirmación
     */
    show(message, title = 'Confirmació', options = {}) {
        return new Promise((resolve) => {
            this.createModal();
            
            const modal = this.modal;
            const overlay = document.getElementById('confirm-overlay');
            const content = document.getElementById('confirm-content');
            const titleEl = document.getElementById('confirm-title');
            const messageEl = document.getElementById('confirm-message');
            const cancelBtn = document.getElementById('confirm-cancel-btn');
            const okBtn = document.getElementById('confirm-ok-btn');
            
            // Configurar contenido
            titleEl.textContent = title;
            messageEl.textContent = message;
            
            // Configurar botones
            cancelBtn.textContent = options.cancelText || 'Cancel·lar';
            okBtn.textContent = options.okText || 'Acceptar';
            
            // Cambiar color del botón OK si es destructivo
            if (options.destructive) {
                okBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'focus:ring-blue-500');
                okBtn.classList.add('bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500');
            } else {
                okBtn.classList.remove('bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500');
                okBtn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'focus:ring-blue-500');
            }
            
            // Mostrar modal con animación
            modal.classList.remove('hidden');
            requestAnimationFrame(() => {
                overlay.style.opacity = '1';
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            });
            
            // Función para cerrar el modal
            const close = (result) => {
                overlay.style.opacity = '0';
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
                
                setTimeout(() => {
                    modal.classList.add('hidden');
                    resolve(result);
                }, 200);
                
                // Limpiar event listeners
                cancelBtn.removeEventListener('click', handleCancel);
                okBtn.removeEventListener('click', handleOk);
                overlay.removeEventListener('click', handleCancel);
                document.removeEventListener('keydown', handleKeydown);
            };
            
            const handleCancel = () => close(false);
            const handleOk = () => close(true);
            const handleKeydown = (e) => {
                if (e.key === 'Escape') handleCancel();
                if (e.key === 'Enter') handleOk();
            };
            
            // Event listeners
            cancelBtn.addEventListener('click', handleCancel);
            okBtn.addEventListener('click', handleOk);
            overlay.addEventListener('click', handleCancel);
            document.addEventListener('keydown', handleKeydown);
        });
    }
};

// Función global para compatibilidad
window.showConfirm = (message, title, options) => {
    return ConfirmModal.show(message, title, options);
};

// Alias
window.Confirm = {
    show: (msg, title, opts) => ConfirmModal.show(msg, title, opts),
    ask: (msg, title, opts) => ConfirmModal.show(msg, title, opts),
    destructive: (msg, title) => ConfirmModal.show(msg, title, { destructive: true })
};
