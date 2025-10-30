/**
 * EzyRide - Componentes Reutilizables
 * Header y Footer compartidos
 */

// Función para obtener la ruta base según la ubicación del archivo
function getBasePath() {
    const path = window.location.pathname;
    if (path.includes('/pages/')) {
        return '../../';
    }
    return './';
}

// Header Component
function createHeader(options = {}) {
    const basePath = getBasePath();
    const { 
        showLogout = false, 
        showBack = false, 
        backUrl = basePath + 'index.html',
        showHelp = true 
    } = options;

    return `
        <header>
            <div class="logo-container">
                <a href="${basePath}index.html" style="display: flex; align-items: center; gap: var(--spacing-md); text-decoration: none;">
                    <img src="${basePath}images/logo.png" alt="Logo EzyRide">
                    <h1>EzyRide</h1>
                </a>
            </div>
            <div class="user-info" style="display: flex; gap: var(--spacing-md); align-items: center;">
                ${showHelp ? `
                    <a href="#" class="btn btn-ghost" style="font-size: 0.875rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        Ajuda
                    </a>
                ` : ''}
                ${showBack ? `
                    <a href="${backUrl}" class="btn btn-ghost">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Tornar
                    </a>
                ` : ''}
                ${showLogout ? `
                    <button id="logoutButton" class="btn btn-ghost">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                        </svg>
                        Tancar Sessió
                    </button>
                ` : ''}
            </div>
        </header>
    `;
}

// Footer Component
function createFooter() {
    return `
        <footer>
            <p>© 2025 EzyRide. Tots els drets reservats.</p>
        </footer>
    `;
}

// Inicializar componentes en la página
function initComponents(options = {}) {
    // Insertar header
    const headerPlaceholder = document.getElementById('header-placeholder');
    if (headerPlaceholder) {
        headerPlaceholder.innerHTML = createHeader(options);
    }

    // Insertar footer
    const footerPlaceholder = document.getElementById('footer-placeholder');
    if (footerPlaceholder) {
        footerPlaceholder.innerHTML = createFooter();
    }

    // Configurar logout si está presente
    if (options.showLogout) {
        setupLogout();
    }
}

// Configurar funcionalidad de logout
function setupLogout() {
    const basePath = getBasePath();
    const logoutButton = document.getElementById('logoutButton');
    if (logoutButton) {
        logoutButton.addEventListener('click', function (e) {
            e.preventDefault();
            fetch(basePath + 'php/api/logout.php', {
                method: 'POST',
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = basePath + 'index.html';
                } else {
                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Error en tancar la sessió.', 'error');
                    } else {
                        alert(data.message || 'Error en tancar la sessió.');
                    }
                }
            })
            .catch(() => {
                if (typeof showToast === 'function') {
                    showToast('Error de connexió amb el servidor.', 'error');
                } else {
                    alert('Error de connexió amb el servidor.');
                }
            });
        });
    }
}
