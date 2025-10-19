/**
 * Footer Script
 * Maneja la interactividad del footer
 */

/**
 * Actualiza el año actual en el footer
 */
function updateFooterYear() {
    const currentYear = new Date().getFullYear();
    const yearElement = document.getElementById('footer-year');
    
    if (yearElement) {
        yearElement.textContent = currentYear;
    }
}

// Ejecutar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    updateFooterYear();
});

// Si el script se carga después del DOMContentLoaded (carga dinámica)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateFooterYear);
} else {
    // DOM ya está listo
    updateFooterYear();
}
