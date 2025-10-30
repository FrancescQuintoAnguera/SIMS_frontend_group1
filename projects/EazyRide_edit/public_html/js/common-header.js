// Header común para todas las páginas
// Agregar al final de cada página: <script src="/js/common-header.js"></script>

document.addEventListener('DOMContentLoaded', function() {
    // Verificar que existe el header
    const header = document.querySelector('header');
    if (!header) return;
    
    // Inicializar selector de idiomas
    initLanguageSelector();
    
    // Inicializar dropdown de perfil si existe
    initProfileDropdown();
});

function initLanguageSelector() {
    const langBtn = document.getElementById('langBtn');
    const langMenu = document.getElementById('langMenu');
    const currentLangText = document.getElementById('currentLangText');
    
    if (!langBtn || !langMenu) return;
    
    // Click en el botón de idioma
    langBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        langMenu.style.display = langMenu.style.display === 'none' ? 'block' : 'none';
        
        // Cerrar menú de perfil si está abierto
        const profileMenu = document.getElementById('profileMenu');
        if (profileMenu) profileMenu.style.display = 'none';
    });
    
    // Cerrar al hacer click fuera
    document.addEventListener('click', function() {
        if (langMenu) langMenu.style.display = 'none';
    });
    
    // Cargar idioma guardado
    const savedLang = localStorage.getItem('preferredLanguage') || 'ca';
    if (currentLangText) {
        currentLangText.textContent = savedLang.toUpperCase();
    }
    
    // Actualizar variable global si existe
    if (typeof window.currentLang !== 'undefined') {
        window.currentLang = savedLang;
    }
}

function initProfileDropdown() {
    const profileBtn = document.getElementById('profileDropdown');
    const profileMenu = document.getElementById('profileMenu');
    
    if (!profileBtn || !profileMenu) return;
    
    profileBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        profileMenu.style.display = profileMenu.style.display === 'none' ? 'block' : 'none';
        
        // Cerrar menú de idiomas si está abierto
        const langMenu = document.getElementById('langMenu');
        if (langMenu) langMenu.style.display = 'none';
    });
    
    // Cerrar al hacer click fuera
    document.addEventListener('click', function() {
        if (profileMenu) profileMenu.style.display = 'none';
    });
}

// Función global para cambiar idioma
window.changeLanguage = function(lang) {
    localStorage.setItem('preferredLanguage', lang);
    
    // Actualizar texto mostrado
    const currentLangText = document.getElementById('currentLangText');
    if (currentLangText) {
        currentLangText.textContent = lang.toUpperCase();
    }
    
    // Actualizar variable global
    if (typeof window.currentLang !== 'undefined') {
        window.currentLang = lang;
    }
    
    // Si existe i18n, cambiar idioma
    if (window.i18n && typeof window.i18n.setLanguage === 'function') {
        window.i18n.setLanguage(lang);
    }
    
    // Cerrar menú
    const langMenu = document.getElementById('langMenu');
    if (langMenu) langMenu.style.display = 'none';
    
    // Mostrar confirmación
    if (typeof showToast === 'function') {
        const messages = {
            'ca': 'Idioma canviat a Català',
            'es': 'Idioma cambiado a Español', 
            'en': 'Language changed to English'
        };
        showToast(messages[lang] || 'Language changed', 'success', 2000);
    }
    
    // Recargar si es necesario
    setTimeout(() => {
        if (window.i18n && typeof window.i18n.translatePage === 'function') {
            window.i18n.translatePage();
        }
    }, 100);
};

console.log('✅ Common header initialized');
