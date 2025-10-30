// Componentes reutilizables: Header y Footer
// Este archivo carga automáticamente el header y footer en todas las páginas

// Función para obtener la ruta relativa correcta
function getBasePath() {
    const path = window.location.pathname;
    if (path.includes('/pages/')) {
        return '../..';
    }
    return '.';
}

// Header Component
function createHeader(options = {}) {
    const basePath = options.basePath || getBasePath();
    const showProfile = options.showProfile !== false;
    const showBack = options.showBack !== false;
    const backUrl = options.backUrl || basePath + '/pages/dashboard/gestio.html';
    
    return `
        <header>
            <div class="logo-container">
                <a href="${backUrl}" style="display: flex; align-items: center; gap: var(--spacing-md); text-decoration: none;">
                    <img src="${basePath}/images/logo.png" alt="Logo EzyRide" style="height: 40px; width: 40px; border-radius: var(--radius-md);">
                    <h1 style="margin: 0; font-size: 1.5rem; font-weight: 700;">EzyRide</h1>
                </a>
            </div>
            <div class="user-info" style="display: flex; align-items: center; gap: var(--spacing-md);">
                <!-- Selector de idioma -->
                <div class="language-selector" style="position: relative;">
                    <button id="langBtn" class="btn btn-ghost" style="padding: var(--spacing-sm) var(--spacing-md); display: flex; align-items: center; gap: var(--spacing-xs);">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="2" y1="12" x2="22" y2="12"/>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                        </svg>
                        <span id="currentLangText">${currentLang.toUpperCase()}</span>
                    </button>
                    <div id="langMenu" class="card-glass" style="position: absolute; top: calc(100% + 8px); right: 0; min-width: 150px; padding: var(--spacing-xs); display: none; z-index: 1000;">
                        <button onclick="changeLanguage('ca')" class="btn btn-ghost" style="width: 100%; justify-content: flex-start; padding: var(--spacing-sm) var(--spacing-md); ${currentLang === 'ca' ? 'background: var(--color-surface-hover);' : ''}">
                            🇪🇸 Català
                        </button>
                        <button onclick="changeLanguage('es')" class="btn btn-ghost" style="width: 100%; justify-content: flex-start; padding: var(--spacing-sm) var(--spacing-md); ${currentLang === 'es' ? 'background: var(--color-surface-hover);' : ''}">
                            🇪🇸 Español
                        </button>
                        <button onclick="changeLanguage('en')" class="btn btn-ghost" style="width: 100%; justify-content: flex-start; padding: var(--spacing-sm) var(--spacing-md); ${currentLang === 'en' ? 'background: var(--color-surface-hover);' : ''}">
                            🇬🇧 English
                        </button>
                    </div>
                </div>
                
                ${showProfile ? `
                    <!-- Botón de perfil -->
                    <a href="${basePath}/pages/profile/perfil.html" class="btn btn-ghost" style="padding: var(--spacing-sm) var(--spacing-md);">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span data-i18n="profile">${t('profile')}</span>
                    </a>
                ` : ''}
                
                ${showBack ? `
                    <a href="${backUrl}" class="btn btn-ghost">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        <span data-i18n="management">${t('management')}</span>
                    </a>
                ` : ''}
            </div>
        </header>
    `;
}

// Footer Component
function createFooter() {
    return `
        <footer style="background: var(--color-bg-secondary); border-top: 1px solid var(--color-border-default); padding: var(--spacing-lg); text-align: center; margin-top: auto;">
            <p style="margin: 0; color: var(--color-text-secondary); font-size: 0.875rem;" data-i18n="rights">
                ${t('rights')}
            </p>
        </footer>
    `;
}

// Función para cargar header y footer automáticamente
function loadHeaderFooter(options = {}) {
    // Cargar Header
    const headerPlaceholder = document.getElementById('header-placeholder');
    if (headerPlaceholder) {
        headerPlaceholder.innerHTML = createHeader(options);
        initLanguageSelector();
    } else {
        // Si no hay placeholder, buscar el header existente
        const header = document.querySelector('header');
        if (!header) {
            // Insertar al inicio del body
            document.body.insertAdjacentHTML('afterbegin', createHeader(options));
            initLanguageSelector();
        }
    }
    
    // Cargar Footer
    const footerPlaceholder = document.getElementById('footer-placeholder');
    if (footerPlaceholder) {
        footerPlaceholder.innerHTML = createFooter();
    } else {
        // Si no hay placeholder, buscar el footer existente
        const footer = document.querySelector('footer');
        if (!footer) {
            // Insertar al final del body
            document.body.insertAdjacentHTML('beforeend', createFooter());
        }
    }
}

// Inicializar selector de idioma
function initLanguageSelector() {
    const langBtn = document.getElementById('langBtn');
    const langMenu = document.getElementById('langMenu');
    
    if (langBtn && langMenu) {
        langBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            langMenu.style.display = langMenu.style.display === 'none' ? 'block' : 'none';
        });
        
        // Cerrar al hacer click fuera
        document.addEventListener('click', () => {
            langMenu.style.display = 'none';
        });
    }
}

// Auto-load cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        // Solo cargar si no hay atributo data-no-auto-layout
        if (!document.body.hasAttribute('data-no-auto-layout')) {
            loadHeaderFooter();
        }
    });
} else {
    if (!document.body.hasAttribute('data-no-auto-layout')) {
        loadHeaderFooter();
    }
}

// Exportar funciones
window.createHeader = createHeader;
window.createFooter = createFooter;
window.loadHeaderFooter = loadHeaderFooter;
