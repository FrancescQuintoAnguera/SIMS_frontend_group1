// header-profile.js - Script para manejar el perfil dinámico en el header
// Este script debe ser incluido en todas las páginas que tengan el dropdown de perfil

(function() {
    'use strict';
    
    // Esperar a que el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeaderProfile);
    } else {
        initHeaderProfile();
    }
    
    function initHeaderProfile() {
        // Inicializar idioma actual si no está definido
        if (typeof window.currentLang === 'undefined') {
            window.currentLang = 'ca';
        }
        
        // Cargar perfil de usuario
        loadUserProfile();
        
        // Inicializar dropdowns
        initDropdowns();
        
        // Actualizar texto de idioma actual
        updateLanguageDisplay();
    }
    
    async function loadUserProfile() {
        try {
            // Determinar la ruta correcta al PHP basándose en la ubicación actual
            const currentPath = window.location.pathname;
            let phpPath = '../../php/user/user-profile.php';
            
            // Ajustar ruta si estamos en una página de auth o raíz
            if (currentPath.includes('/auth/')) {
                phpPath = '../../php/user/user-profile.php';
            } else if (currentPath.includes('/pages/')) {
                phpPath = '../../php/user/user-profile.php';
            }
            
            const response = await fetch(phpPath, {
                credentials: 'include',
                headers: {
                    'Cache-Control': 'no-cache'
                }
            });
            
            if (!response.ok) {
                console.warn('No se pudo cargar el perfil de usuario');
                return;
            }
            
            const data = await response.json();
            
            if (data.success && data.data) {
                const username = data.data.username || 'Usuario';
                
                // Actualizar nombre de usuario
                const usernameEl = document.getElementById('profileUsername');
                if (usernameEl) {
                    usernameEl.textContent = username;
                }
                
                // Actualizar avatar con iniciales
                const initial = username.charAt(0).toUpperCase();
                const avatarEl = document.getElementById('profileAvatar');
                if (avatarEl) {
                    avatarEl.textContent = initial;
                }
            }
        } catch (error) {
            console.error('Error cargando perfil de usuario:', error);
        }
    }
    
    function initDropdowns() {
        const profileDropdown = document.getElementById('profileDropdown');
        const profileMenu = document.getElementById('profileMenu');
        const langBtn = document.getElementById('langBtn');
        const langMenu = document.getElementById('langMenu');
        
        // Dropdown de perfil
        if (profileDropdown && profileMenu) {
            profileDropdown.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = profileMenu.style.display !== 'none';
                profileMenu.style.display = isOpen ? 'none' : 'block';
                if (langMenu) langMenu.style.display = 'none';
            });
        }
        
        // Dropdown de idioma
        if (langBtn && langMenu) {
            langBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = langMenu.style.display !== 'none';
                langMenu.style.display = isOpen ? 'none' : 'block';
                if (profileMenu) profileMenu.style.display = 'none';
            });
        }
        
        // Cerrar dropdowns al hacer clic fuera
        document.addEventListener('click', () => {
            if (langMenu) langMenu.style.display = 'none';
            if (profileMenu) profileMenu.style.display = 'none';
        });
    }
    
    function updateLanguageDisplay() {
        const currentLangText = document.getElementById('currentLangText');
        if (currentLangText && window.currentLang) {
            currentLangText.textContent = window.currentLang.toUpperCase();
        }
    }
    
    // Función de logout global
    window.logout = function() {
        const currentPath = window.location.pathname;
        let logoutPath = '../../php/auth/logout.php';
        let loginPath = '../auth/login.html';
        
        // Ajustar rutas según ubicación
        if (currentPath.includes('/auth/')) {
            logoutPath = '../../php/auth/logout.php';
            loginPath = 'login.html';
        }
        
        fetch(logoutPath, {
            method: 'POST',
            credentials: 'include'
        })
        .then(() => {
            window.location.href = loginPath;
        })
        .catch(error => {
            console.error('Error al cerrar sesión:', error);
            window.location.href = loginPath;
        });
    };
    
})();
