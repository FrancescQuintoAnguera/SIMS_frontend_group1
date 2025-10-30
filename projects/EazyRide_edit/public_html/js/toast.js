class ToastNotification {
    constructor() {
        this.container = null;
        this.init();
    }
    
    init() {
        // Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.createContainer());
        } else {
            // Asegurar que body existe antes de crear el contenedor
            if (document.body) {
                this.createContainer();
            } else {
                window.addEventListener('load', () => this.createContainer());
            }
        }
    }
    
    createContainer() {
        // Asegurar que body existe
        if (!document.body) {
            setTimeout(() => this.createContainer(), 10);
            return;
        }
        
        // Verificar si ya existe el contenedor
        const existingContainer = document.getElementById('toast-container');
        if (existingContainer) {
            this.container = existingContainer;
            return;
        }
        
        // Crear nuevo contenedor
        this.container = document.createElement('div');
        this.container.id = 'toast-container';
        this.container.className = 'toast-container';
        this.container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000; display: flex; flex-direction: column; gap: 10px; max-width: 400px;';
        document.body.appendChild(this.container);
    }
    
    show(message, type = 'info', duration = 3000) {
        // Asegurar que el contenedor existe
        if (!this.container) {
            this.createContainer();
        }
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        const icons = { 
            success: '✓', 
            error: '✕', 
            warning: '⚠', 
            info: 'ℹ' 
        };
        
        const colors = {
            success: 'linear-gradient(135deg, #00C853 0%, #00A845 100%)',
            error: 'linear-gradient(135deg, #FF3D00 0%, #D50000 100%)',
            warning: 'linear-gradient(135deg, #FFD700 0%, #FFC107 100%)',
            info: 'linear-gradient(135deg, #69B7F0 0%, #4A9FE8 100%)'
        };
        
        toast.style.cssText = `
            background: ${colors[type] || colors.info};
            color: white;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            font-weight: 500;
        `;
        
        toast.innerHTML = `
            <div style="font-size: 20px; font-weight: bold;">${icons[type] || icons.info}</div>
            <div style="flex: 1;">${message}</div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; opacity: 0.8; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">×</button>
        `;
        
        this.container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        }, 10);
        
        if (duration > 0) {
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
        
        return toast;
    }
    
    success(message, duration = 3000) { return this.show(message, 'success', duration); }
    error(message, duration = 4000) { return this.show(message, 'error', duration); }
    warning(message, duration = 3500) { return this.show(message, 'warning', duration); }
    info(message, duration = 3000) { return this.show(message, 'info', duration); }
}

// Inicializar cuando el DOM esté listo (solo una vez)
if (!window.toast) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.toast) {
                window.toast = new ToastNotification();
            }
        });
    } else {
        window.toast = new ToastNotification();
    }
}

window.showToast = function(message, type = 'info', duration, replacements = {}) {
    // Si message parece una clave de traducción
    if (message && !message.includes(' ') && (message.includes('.') || window.i18n)) {
        const translated = window.i18n ? window.i18n.translate(message, replacements) : message;
        if (translated && translated !== message) {
            message = translated;
        }
    }
    
    // Reemplazar variables si las hay
    if (replacements && typeof replacements === 'object') {
        Object.keys(replacements).forEach(key => {
            message = message.replace(new RegExp(`{${key}}`, 'g'), replacements[key]);
        });
    }
    
    // Asegurar que toast existe
    if (!window.toast) {
        window.toast = new ToastNotification();
    }
    
    return window.toast.show(message, type, duration);
};
