<?php
/**
 * Header Component
 * Reusable header with navigation bar
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="VoltiaCar - Carsharing Service">
    <title><?php echo $pageTitle ?? 'VoltiaCar'; ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Tailwind Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-green': '#10b981',
                        'primary-green-dark': '#059669',
                        'primary-blue': '#3b82f6',
                        'primary-blue-dark': '#2563eb',
                        'gray-custom': '#6b7280',
                        'gray-light': '#9ca3af'
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/custom.css">
    <link rel="stylesheet" href="/css/accessibility.css">
    
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo and Brand -->
                <div class="flex items-center">
                    <a href="/index.php" class="flex items-center space-x-2">
                        <img src="/images/logo.png" alt="VoltiaCar Logo" class="h-10 w-10 rounded-full">
                        <span class="text-xl font-bold text-primary-green">VoltiaCar</span>
                    </a>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-6">
                    <?php if ($isLoggedIn): ?>
                        <!-- Botón Inicio -->
                        <a href="/pages/dashboard/gestio.html" class="flex items-center space-x-1 text-gray-700 hover:text-primary-green transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span>Inici</span>
                        </a>
                        
                        <!-- Logged in menu -->
                        <a href="/pages/vehicle/localitzar-vehicle.html" class="text-gray-700 hover:text-primary-green transition-colors">
                            Localitzar Vehicle
                        </a>
                        <a href="/pages/dashboard/historial.html" class="text-gray-700 hover:text-primary-green transition-colors">
                            Historial
                        </a>
                    <?php else: ?>
                        <!-- Guest menu -->
                        <a href="/pages/auth/login.html" class="text-gray-700 hover:text-primary-green transition-colors">
                            Iniciar Sessió
                        </a>
                        <a href="/pages/auth/register.html" class="text-gray-700 hover:text-primary-green transition-colors">
                            Registrar-se
                        </a>
                    <?php endif; ?>
                    
                    <!-- Selector de Idiomas -->
                    <div class="relative">
                        <button id="langDropdown" class="flex items-center space-x-1 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                            </svg>
                            <span id="currentLangHeader" class="text-sm font-medium text-gray-700">CA</span>
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div id="langMenu" class="hidden absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg py-2 z-50">
                            <button onclick="changeLanguage('ca')" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 flex items-center space-x-2">
                                <span class="font-medium">🇪🇸</span>
                                <span>Català</span>
                            </button>
                            <button onclick="changeLanguage('es')" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 flex items-center space-x-2">
                                <span class="font-medium">🇪🇸</span>
                                <span>Español</span>
                            </button>
                            <button onclick="changeLanguage('en')" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 flex items-center space-x-2">
                                <span class="font-medium">🇬🇧</span>
                                <span>English</span>
                            </button>
                        </div>
                    </div>
                    
                    <?php if ($isLoggedIn): ?>
                        <!-- User Profile Dropdown -->
                        <div class="relative">
                            <button id="profileDropdown" class="flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 bg-gradient-to-br from-primary-green to-primary-blue rounded-full flex items-center justify-center text-white font-semibold">
                                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                                </div>
                                <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($username); ?></span>
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div id="profileMenu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 z-50">
                                <a href="/pages/profile/perfil.html" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span>El meu perfil</span>
                                    </div>
                                </a>
                                <a href="/pages/vehicle/purchase-time.html" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Comprar EazyPoints</span>
                                    </div>
                                </a>
                                <a href="/pages/profile/premium.html" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                        <span>Premium</span>
                                    </div>
                                </a>
                                <div class="border-t border-gray-200 my-2"></div>
                                <button id="logoutBtn" class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100 flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    <span>Tancar sessió</span>
                                </button>
                            </div>
                        </div>
                        <script>
                            // Dropdown de perfil
                            document.getElementById('profileDropdown')?.addEventListener('click', function(e) {
                                e.stopPropagation();
                                const menu = document.getElementById('profileMenu');
                                menu.classList.toggle('hidden');
                                // Cerrar el menú de idiomas si está abierto
                                document.getElementById('langMenu')?.classList.add('hidden');
                            });
                            
                            // Dropdown de idiomas
                            document.getElementById('langDropdown')?.addEventListener('click', function(e) {
                                e.stopPropagation();
                                const menu = document.getElementById('langMenu');
                                menu.classList.toggle('hidden');
                                // Cerrar el menú de perfil si está abierto
                                document.getElementById('profileMenu')?.classList.add('hidden');
                            });
                            
                            // Cerrar dropdowns al hacer click fuera
                            document.addEventListener('click', function() {
                                document.getElementById('profileMenu')?.classList.add('hidden');
                                document.getElementById('langMenu')?.classList.add('hidden');
                            });
                            
                            // Función para cambiar idioma
                            window.changeLanguage = function(lang) {
                                localStorage.setItem('preferredLanguage', lang);
                                document.getElementById('currentLangHeader').textContent = lang.toUpperCase();
                                
                                // Si existe la variable global currentLang, actualizarla
                                if (typeof window.currentLang !== 'undefined') {
                                    window.currentLang = lang;
                                }
                                
                                // Si existe i18n, cambiar idioma
                                if (window.i18n && typeof window.i18n.setLanguage === 'function') {
                                    window.i18n.setLanguage(lang);
                                }
                                
                                // Cerrar menú
                                document.getElementById('langMenu')?.classList.add('hidden');
                                
                                // Recargar la página para aplicar el idioma
                                location.reload();
                            };
                            
                            // Cargar idioma guardado al inicio
                            document.addEventListener('DOMContentLoaded', function() {
                                const savedLang = localStorage.getItem('preferredLanguage') || 'ca';
                                const currentLangEl = document.getElementById('currentLangHeader');
                                if (currentLangEl) {
                                    currentLangEl.textContent = savedLang.toUpperCase();
                                }
                            });
                        </script>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Selector de idiomas para usuarios no autenticados -->
                    <div class="relative">
                        <button id="langDropdownGuest" class="flex items-center space-x-1 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                            </svg>
                            <span id="currentLangHeaderGuest" class="text-sm font-medium text-gray-700">CA</span>
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div id="langMenuGuest" class="hidden absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg py-2 z-50">
                            <button onclick="changeLanguage('ca')" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 flex items-center space-x-2">
                                <span class="font-medium">🇪🇸</span>
                                <span>Català</span>
                            </button>
                            <button onclick="changeLanguage('es')" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 flex items-center space-x-2">
                                <span class="font-medium">🇪🇸</span>
                                <span>Español</span>
                            </button>
                            <button onclick="changeLanguage('en')" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 flex items-center space-x-2">
                                <span class="font-medium">🇬🇧</span>
                                <span>English</span>
                            </button>
                        </div>
                    </div>
                    <script>
                        // Dropdown de idiomas para invitados
                        document.getElementById('langDropdownGuest')?.addEventListener('click', function(e) {
                            e.stopPropagation();
                            const menu = document.getElementById('langMenuGuest');
                            menu.classList.toggle('hidden');
                        });
                        
                        document.addEventListener('click', function() {
                            document.getElementById('langMenuGuest')?.classList.add('hidden');
                        });
                        
                        // Cargar idioma guardado al inicio
                        document.addEventListener('DOMContentLoaded', function() {
                            const savedLang = localStorage.getItem('preferredLanguage') || 'ca';
                            const currentLangEl = document.getElementById('currentLangHeaderGuest');
                            if (currentLangEl) {
                                currentLangEl.textContent = savedLang.toUpperCase();
                            }
                        });
                    </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
