# 🏗️ Nueva Estructura VoltiaCar - Estilo Eazy Ride

## ✅ Reorganización Completada

VoltiaCar ahora tiene la **misma estructura de carpetas** que Eazy Ride (SIMS Frontend Group 1).

---

## 📁 Estructura de Directorios

```
src/
├── index.php                 # Punto de entrada principal
├── api/                      # API endpoints
├── auth/                     # Sistema de autenticación
│   └── auth.js
├── router/                   # Sistema de enrutamiento
│   └── router.js
├── data/                     # Datos JSON
├── common/                   # Componentes comunes/reutilizables
│   ├── style/
│   │   └── common.css       # Estilos globales
│   ├── images/              # Imágenes compartidas
│   │   └── logo.png
│   ├── scripts/
│   │   └── main.js          # Script principal
│   └── modules/             # Módulos comunes
│       ├── navBar/
│       │   ├── template/
│       │   │   └── navbar.php
│       │   ├── styles/
│       │   │   └── navbar.css
│       │   └── scripts/
│       │       └── navbar.js
│       ├── footer/
│       │   ├── template/
│       │   │   └── footer.php
│       │   └── styles/
│       │       └── footer.css
│       └── sidebar/
│           ├── templates/
│           │   └── sidebar.php
│           └── scripts/
│               └── sidebar.js
└── modules/                  # Módulos de páginas
    ├── login/
    │   ├── template/
    │   │   └── login.php
    │   ├── styles/
    │   │   └── login.css
    │   └── script/
    │       └── login.js
    ├── register/
    │   ├── template/
    │   │   └── register.php
    │   ├── styles/
    │   │   └── register.css
    │   └── script/
    │       └── register.js
    ├── home/
    │   ├── template/
    │   │   └── home.php
    │   └── styles/
    │       └── home.css
    └── localitzar/
        ├── template/
        │   └── localitzar.php
        ├── styles/
        │   └── localitzar.css
        └── script/
            └── localitzar.js
```

---

## 🎯 Arquitectura

### Punto de Entrada: `index.php`
- Carga el layout principal
- Incluye header (navbar + sidebar)
- Área principal `#app` para contenido dinámico
- Incluye footer
- Carga scripts: auth, router, navbar, main

### Sistema de Routing
- **Router SPA**: `router/router.js`
- Navegación por hash: `#login`, `#home`, `#localitzar`
- Carga dinámica de módulos

### Autenticación
- **Auth Manager**: `auth/auth.js`
- Gestión de tokens (localStorage + cookies)
- Validación de sesiones
- Login/Logout

---

## 🎨 Sistema de Estilos

### CSS Global: `common/style/common.css`
- Fuente Poppins
- Variables de tema oscuro
- Clases utilitarias (buttons, cards, forms)
- Layout base (header, main, footer)

### CSS por Módulo
Cada módulo tiene su propio archivo CSS:
- `modules/login/styles/login.css`
- `modules/register/styles/register.css`
- `modules/home/styles/home.css`
- `modules/localitzar/styles/localitzar.css`

### Componentes Comunes
- **NavBar**: `common/modules/navBar/`
- **Footer**: `common/modules/footer/`
- **Sidebar**: `common/modules/sidebar/`

---

## 🚀 Cómo Funciona

### 1. Carga Inicial
```
Usuario → index.php → Carga layout → Router.js → Carga #login
```

### 2. Navegación
```javascript
// Navegar a otra página
window.navigate('home');

// O por hash
window.location.hash = '#localitzar';
```

### 3. Autenticación
```javascript
// Login
const result = await window.auth.login(username, password);

// Logout
window.auth.logout();

// Comprobar si está autenticado
if (window.auth.isAuthenticated()) {
    // Usuario logueado
}
```

### 4. Notificaciones
```javascript
// Mostrar toast
window.showToast('Mensaje', 'success'); // success, error, warning, info
```

---

## 📝 Convenciones de Código

### Estructura de un Módulo
```
modules/nombre-modulo/
├── template/
│   └── nombre-modulo.php    # HTML del módulo
├── styles/
│   └── nombre-modulo.css    # CSS específico
└── script/
    └── nombre-modulo.js     # JavaScript específico
```

### Template PHP
```php
<link rel="stylesheet" href="/modules/nombre-modulo/styles/nombre-modulo.css">

<div class="nombre-modulo-container">
    <!-- Contenido -->
</div>

<script src="/modules/nombre-modulo/script/nombre-modulo.js"></script>
```

### CSS del Módulo
```css
.nombre-modulo-container {
    /* Estilos del contenedor principal */
}

/* Usa las clases globales */
.btn-primary { ... }
.card { ... }
.form-input { ... }
```

### JavaScript del Módulo
```javascript
document.addEventListener('DOMContentLoaded', () => {
    // Código del módulo
});
```

---

## 🎨 Tema Oscuro

### Colores Principales
```css
/* Fondos */
#0D2136  /* Fondo principal */
#1D3854  /* Cards, contenedores */
#3B3F48  /* Header */
#132B43  /* Inputs */
#2E4A68  /* Cards oscuros */

/* Acentos */
#A6EE36  /* Verde lima - Botones */
#69B7F0  /* Azul claro - Links */

/* Texto */
#FFFFFF  /* Texto principal */
#9CA3AF  /* Texto secundario */
```

---

## 🔧 Configuración del Servidor

### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^index\.php$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /src/index.php [L]
</IfModule>
```

### Nginx
```nginx
location / {
    try_files $uri $uri/ /src/index.php?$args;
}
```

---

## 📦 Archivos Creados

### PHP Templates (8 archivos)
- index.php
- navBar/template/navbar.php
- footer/template/footer.php
- sidebar/templates/sidebar.php
- login/template/login.php
- register/template/register.php
- home/template/home.php
- localitzar/template/localitzar.php

### CSS (7 archivos)
- common/style/common.css
- navBar/styles/navbar.css
- footer/styles/footer.css
- login/styles/login.css
- register/styles/register.css
- home/styles/home.css
- localitzar/styles/localitzar.css

### JavaScript (6 archivos)
- auth/auth.js
- router/router.js
- common/scripts/main.js
- navBar/scripts/navbar.js
- sidebar/scripts/sidebar.js
- login/script/login.js
- register/script/register.js

---

## 🚀 Puesta en Marcha

### 1. Configurar servidor web
Apuntar el DocumentRoot a `/Users/ganso/Desktop/voltiacar_develop/src/`

### 2. Abrir en navegador
```
http://localhost/
```

### 3. Navegar
- Login: `http://localhost/#login`
- Register: `http://localhost/#register`
- Home: `http://localhost/#home`
- Localizar: `http://localhost/#localitzar`

---

## ✨ Ventajas de esta Estructura

✅ **Modular**: Cada página es un módulo independiente
✅ **Escalable**: Fácil añadir nuevos módulos
✅ **Mantenible**: Código organizado por funcionalidad
✅ **SPA**: Single Page Application con routing
✅ **Reutilizable**: Componentes comunes compartidos
✅ **Consistente**: Mismo patrón en toda la app

---

## 📖 Próximos Pasos

1. [ ] Implementar API endpoints en `/api/`
2. [ ] Completar módulo localitzar con mapa Leaflet
3. [ ] Añadir módulos: perfil, historial, mis-vehicles
4. [ ] Integrar con backend real
5. [ ] Añadir tests

---

**Fecha:** 2025-10-22  
**Versión:** 3.0 - Estructura Modular  
**Inspirado en:** SIMS (Eazy Ride) Frontend Architecture
