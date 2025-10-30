#!/usr/bin/env python3
"""
Script para copiar el header de perfil.html a todas las demás páginas HTML
"""

import os
import re
from pathlib import Path

# Header completo de perfil.html (líneas 12-91)
HEADER_HTML = '''    <header>
        <div class="logo-container">
            <a href="../dashboard/gestio.html" style="display: flex; align-items: center; gap: var(--spacing-md); text-decoration: none;">
                <img src="../../images/logo.png" alt="Logo Eazy Ride" style="height: 40px; width: 40px; border-radius: var(--radius-md);">
                <h1 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Eazy Ride</h1>
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
                    <span id="currentLangText">CA</span>
                </button>
                <div id="langMenu" class="card-glass" style="position: absolute; top: calc(100% + 8px); right: 0; min-width: 150px; padding: var(--spacing-xs); display: none; z-index: 1000;">
                    <button onclick="changeLanguage('ca')" class="btn btn-ghost" style="width: 100%; justify-content: flex-start; padding: var(--spacing-sm) var(--spacing-md);">
                        🇪🇸 Català
                    </button>
                    <button onclick="changeLanguage('es')" class="btn btn-ghost" style="width: 100%; justify-content: flex-start; padding: var(--spacing-sm) var(--spacing-md);">
                        🇪🇸 Español
                    </button>
                    <button onclick="changeLanguage('en')" class="btn btn-ghost" style="width: 100%; justify-content: flex-start; padding: var(--spacing-sm) var(--spacing-md);">
                        🇬🇧 English
                    </button>
                </div>
            </div>
            
            <!-- Dropdown de perfil -->
            <div style="position: relative;">
                <button id="profileDropdown" class="btn btn-ghost" style="display: flex; align-items: center; gap: var(--spacing-sm);">
                    <div id="profileAvatar" style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--color-accent-primary) 0%, var(--color-accent-secondary) 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.875rem;">
                        U
                    </div>
                    <span id="profileUsername">Cargando...</span>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="profileMenu" class="card-glass" style="position: absolute; top: calc(100% + 8px); right: 0; min-width: 220px; padding: var(--spacing-xs); display: none; z-index: 1000;">
                    <a href="../profile/perfil.html" class="btn btn-ghost" style="width: 100%; justify-content: flex-start; padding: var(--spacing-sm) var(--spacing-md);">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>El meu perfil</span>
                    </a>
                    <a href="../vehicle/purchase-time.html" class="btn btn-ghost" style="width: 100%; justify-content: flex-start; padding: var(--spacing-sm) var(--spacing-md);">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Comprar Punts</span>
                    </a>
                    <a href="../profile/premium.html" class="btn btn-ghost" style="width: 100%; justify-content: flex-start; padding: var(--spacing-sm) var(--spacing-md);">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        <span>Premium</span>
                    </a>
                    <hr style="margin: var(--spacing-sm) 0; border: none; border-top: 1px solid rgba(255,255,255,0.1);">
                    <button onclick="logout()" class="btn btn-ghost" style="width: 100%; justify-content: flex-start; padding: var(--spacing-sm) var(--spacing-md); color: #EF4444;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Tancar sessió</span>
                    </button>
                </div>
            </div>
            
            <a href="../dashboard/gestio.html" class="btn btn-ghost">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                <span data-i18n="management">Gestió</span>
            </a>
        </div>
    </header>
'''

SCRIPTS_JS = '''
// Inicializar dropdown de perfil
function initProfileDropdown() {
  const profileBtn = document.getElementById('profileDropdown');
  const profileMenu = document.getElementById('profileMenu');
  const langMenu = document.getElementById('langMenu');
  
  if (profileBtn && profileMenu) {
    profileBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      profileMenu.style.display = profileMenu.style.display === 'none' ? 'block' : 'none';
      if (langMenu) langMenu.style.display = 'none';
    });
  }
  
  document.addEventListener('click', function() {
    if (profileMenu) profileMenu.style.display = 'none';
    if (langMenu) langMenu.style.display = 'none';
  });
  
  console.log('✅ Dropdown de perfil inicializado');
}

// Cargar nombre de usuario
function loadUsername() {
  fetch('../../php/api/session-check.php', { 
    credentials: 'include' 
  })
  .then(res => res.json())
  .then(data => {
    if (data.loggedIn && data.username) {
      const usernameEl = document.getElementById('profileUsername');
      const avatarEl = document.getElementById('profileAvatar');
      
      if (usernameEl) usernameEl.textContent = data.username;
      if (avatarEl) avatarEl.textContent = data.username.charAt(0).toUpperCase();
      
      console.log('✅ Usuario cargado:', data.username);
    }
  })
  .catch(err => console.error('Error cargando usuario:', err));
}

// Función para cerrar sesión
function logout() {
  if (confirm('Estàs segur que vols tancar la sessió?')) {
    fetch('../../php/api/logout.php', { 
      method: 'POST',
      credentials: 'include' 
    })
    .then(() => {
      if (typeof showToast === 'function') {
        showToast('Sessió tancada correctament', 'success');
      }
      setTimeout(() => {
        window.location.href = '../auth/login.html';
      }, 1000);
    })
    .catch(err => {
      console.error('Error al cerrar sesión:', err);
      window.location.href = '../auth/login.html';
    });
  }
}

// Función changeLanguage
if (typeof window.changeLanguage === 'undefined') {
  window.changeLanguage = function(lang) {
    localStorage.setItem('preferredLanguage', lang);
    const currentLangText = document.getElementById('currentLangText');
    if (currentLangText) {
      currentLangText.textContent = lang.toUpperCase();
    }
    if (typeof showToast === 'function') {
      const messages = {
        'ca': 'Idioma canviat a Català',
        'es': 'Idioma cambiado a Español', 
        'en': 'Language changed to English'
      };
      showToast(messages[lang], 'success', 2000);
    }
    const langMenu = document.getElementById('langMenu');
    if (langMenu) langMenu.style.display = 'none';
  };
}

// Inicializar al cargar
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function() {
    initProfileDropdown();
    loadUsername();
  });
} else {
  initProfileDropdown();
  loadUsername();
}
'''

def process_html_file(filepath):
    """Procesa un archivo HTML y actualiza su header"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Buscar y reemplazar el header existente
        # Patrón: desde <header> hasta </header>
        header_pattern = r'<header>.*?</header>'
        
        if not re.search(header_pattern, content, re.DOTALL):
            print(f"  ⚠️  No se encontró <header> en {filepath}")
            return False
        
        # Reemplazar header
        new_content = re.sub(header_pattern, HEADER_HTML.strip(), content, flags=re.DOTALL)
        
        # Verificar si ya tiene las funciones
        if 'function initProfileDropdown()' not in new_content:
            # Buscar el último </script> antes de </body>
            script_pattern = r'(</script>\s*</body>)'
            if re.search(script_pattern, new_content):
                new_content = re.sub(
                    script_pattern,
                    SCRIPTS_JS + r'\n    </script>\n</body>',
                    new_content,
                    count=1
                )
            else:
                # Si no hay </script></body>, agregar antes de </body>
                body_pattern = r'(</body>)'
                new_content = re.sub(
                    body_pattern,
                    r'<script>' + SCRIPTS_JS + r'</script>\n</body>',
                    new_content,
                    count=1
                )
        
        # Guardar archivo
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        
        print(f"  ✅ Actualizado: {filepath}")
        return True
        
    except Exception as e:
        print(f"  ❌ Error en {filepath}: {e}")
        return False

def main():
    base_path = Path('/Users/ganso/Desktop/EazyRide_edit/public_html/pages')
    
    # Páginas a procesar (excluyendo perfil.html y auth)
    html_files = []
    for root, dirs, files in os.walk(base_path):
        # Excluir carpeta auth
        if 'auth' in root:
            continue
        for file in files:
            if file.endswith('.html') and file != 'perfil.html':
                filepath = os.path.join(root, file)
                html_files.append(filepath)
    
    print(f"\n🔍 Encontrados {len(html_files)} archivos HTML para procesar\n")
    
    updated = 0
    for filepath in html_files:
        if process_html_file(filepath):
            updated += 1
    
    print(f"\n✅ Proceso completado:")
    print(f"   Total archivos: {len(html_files)}")
    print(f"   Actualizados: {updated}")
    print(f"   Fallidos: {len(html_files) - updated}\n")

if __name__ == '__main__':
    main()
