# 🎨 Guía de Componentes - Eazy Ride

## 📐 Layouts

### Container Básico
```html
<main>
    <div class="container fade-in">
        <!-- Contenido aquí (max-width: 480px) -->
    </div>
</main>
```

### Container Grande
```html
<main>
    <div class="container container-large fade-in">
        <!-- Contenido aquí (max-width: 900px) -->
    </div>
</main>
```

### Container Full
```html
<main>
    <div class="container container-full fade-in">
        <!-- Contenido aquí (max-width: 1200px) -->
    </div>
</main>
```

## 🎯 Headers

### Header con Logout
```html
<header>
    <div class="logo-container">
        <a href="../dashboard/gestio.html" style="display: flex; align-items: center; gap: var(--spacing-md); text-decoration: none;">
            <img src="../../images/logo.png" alt="Logo Eazy Ride">
            <h1>Eazy Ride</h1>
        </a>
    </div>
    <div class="user-info">
        <button id="logoutButton" class="btn btn-ghost">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
            </svg>
            Tancar Sessió
        </button>
    </div>
</header>
```

### Header con Botón de Retorno
```html
<header>
    <div class="logo-container">
        <a href="../../index.html" style="display: flex; align-items: center; gap: var(--spacing-md); text-decoration: none;">
            <img src="../../images/logo.png" alt="Logo Eazy Ride">
            <h1>Eazy Ride</h1>
        </a>
    </div>
    <div class="user-info">
        <a href="../dashboard/gestio.html" class="btn btn-ghost">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Tornar
        </a>
    </div>
</header>
```

## 🏷️ Títulos de Página

### Título con Icono Grande
```html
<div style="text-align: center; margin-bottom: var(--spacing-2xl);">
    <div class="icon-container icon-container-lg" style="background: linear-gradient(135deg, var(--color-accent-primary) 0%, var(--color-accent-secondary) 100%);">
        <svg width="50" height="50" fill="none" stroke="white" stroke-width="2">
            <!-- Tu icono SVG aquí -->
        </svg>
    </div>
    <h2 style="margin-bottom: var(--spacing-sm);">Título Principal</h2>
    <p style="color: var(--color-text-secondary);">Descripción o subtítulo</p>
</div>
```

### Título Simple
```html
<div style="text-align: center; margin-bottom: var(--spacing-xl);">
    <h2 style="margin-bottom: var(--spacing-sm);">Título</h2>
    <p style="color: var(--color-text-secondary);">Descripción</p>
</div>
```

## 🎴 Cards

### Card Glass Básica
```html
<div class="card-glass">
    <h3>Título de la Card</h3>
    <p>Contenido de la card</p>
</div>
```

### Card con Icono y Descripción
```html
<div class="card-glass" style="padding: var(--spacing-lg);">
    <div style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-sm);">
        <svg width="32" height="32" fill="none" stroke="var(--color-accent-primary)" stroke-width="2">
            <!-- Icono -->
        </svg>
        <div style="flex: 1;">
            <p style="font-weight: 600; font-size: 1.125rem; margin: 0;">Título</p>
            <p style="font-size: 0.875rem; color: var(--color-text-secondary); margin: 0;">Descripción</p>
        </div>
    </div>
</div>
```

### Card Clickeable (Link)
```html
<a href="url" class="card-glass" style="text-decoration: none; padding: var(--spacing-xl); cursor: pointer;">
    <div class="icon-container">
        <svg width="30" height="30" fill="none" stroke="white" stroke-width="2">
            <!-- Icono -->
        </svg>
    </div>
    <h3 style="text-align: center; margin-bottom: var(--spacing-sm);">Título</h3>
    <p style="text-align: center; color: var(--color-text-secondary); font-size: 0.875rem;">
        Descripción
    </p>
</a>
```

## 🔘 Botones

### Botón Primario
```html
<button class="btn btn-primary" style="width: 100%;">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
        <!-- Icono opcional -->
    </svg>
    Texto del Botón
</button>
```

### Botón Secundario
```html
<button class="btn btn-secondary">
    Acción Secundaria
</button>
```

### Botón Ghost
```html
<button class="btn btn-ghost">
    Cancelar
</button>
```

### Grupo de Botones
```html
<div style="display: flex; gap: var(--spacing-md);">
    <button class="btn btn-secondary" style="flex: 1;">Cancelar</button>
    <button class="btn btn-primary" style="flex: 1;">Confirmar</button>
</div>
```

## 📋 Formularios

### Input de Texto
```html
<div class="form-group">
    <label for="inputId" class="form-label">Etiqueta</label>
    <input type="text" id="inputId" class="form-input" placeholder="Placeholder" required>
</div>
```

### Select
```html
<div class="form-group">
    <label for="selectId" class="form-label">Selecciona</label>
    <select id="selectId" class="form-input">
        <option value="">Selecciona una opció</option>
        <option value="1">Opció 1</option>
        <option value="2">Opció 2</option>
    </select>
</div>
```

### Textarea
```html
<div class="form-group">
    <label for="textareaId" class="form-label">Missatge</label>
    <textarea id="textareaId" class="form-input" rows="4" placeholder="Escriu aquí..."></textarea>
</div>
```

### Formulario Completo
```html
<form id="myForm">
    <div class="form-group">
        <label for="name" class="form-label">Nom</label>
        <input type="text" id="name" class="form-input" required>
    </div>
    
    <div class="form-group">
        <label for="email" class="form-label">Correu</label>
        <input type="email" id="email" class="form-input" required>
    </div>
    
    <button type="submit" class="btn btn-primary" style="width: 100%;">
        Enviar
    </button>
</form>
```

## 🏷️ Badges

### Badge Normal
```html
<span class="badge">Normal</span>
```

### Badge Success
```html
<span class="badge badge-success">Actiu</span>
```

### Badge Error
```html
<span class="badge badge-error">Error</span>
```

### Badge Warning
```html
<span class="badge badge-warning">Advertència</span>
```

## 📊 Barras de Progreso

### Barra Simple
```html
<div style="width: 100%; background: var(--color-surface); border-radius: var(--radius-full); height: 32px; overflow: hidden;">
    <div style="width: 75%; height: 100%; background: linear-gradient(135deg, var(--color-accent-primary) 0%, var(--color-accent-secondary) 100%); display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--color-bg-primary);">
        75%
    </div>
</div>
```

### Barra con Label
```html
<div class="card-glass">
    <p style="font-size: 0.875rem; color: var(--color-text-secondary); margin-bottom: var(--spacing-sm);">
        Progreso
    </p>
    <div style="width: 100%; background: var(--color-surface); border-radius: var(--radius-full); height: 32px;">
        <div style="width: 95%; height: 100%; background: linear-gradient(135deg, var(--color-accent-primary) 0%, #69B7F0 100%); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--color-bg-primary);">
            95%
        </div>
    </div>
</div>
```

## 🗂️ Grids

### Grid Automático
```html
<div class="grid grid-auto" style="gap: var(--spacing-lg);">
    <div class="card-glass">Item 1</div>
    <div class="card-glass">Item 2</div>
    <div class="card-glass">Item 3</div>
    <div class="card-glass">Item 4</div>
</div>
```

### Grid 2 Columnas
```html
<div class="grid grid-2" style="gap: var(--spacing-lg);">
    <div class="card-glass">Columna 1</div>
    <div class="card-glass">Columna 2</div>
</div>
```

### Grid 3 Columnas
```html
<div class="grid grid-3" style="gap: var(--spacing-lg);">
    <div class="card-glass">1</div>
    <div class="card-glass">2</div>
    <div class="card-glass">3</div>
</div>
```

## 📱 Alertas e Información

### Alert de Información
```html
<div class="card-glass" style="background: rgba(105, 183, 240, 0.1); border: 1px solid rgba(105, 183, 240, 0.3); padding: var(--spacing-md); display: flex; align-items: center; gap: var(--spacing-md);">
    <svg width="24" height="24" fill="none" stroke="var(--color-accent-secondary)" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <p style="font-size: 0.875rem; color: var(--color-text-secondary); margin: 0;">
        Mensaje informativo aquí
    </p>
</div>
```

### Alert de Advertencia
```html
<div class="card-glass" style="background: rgba(255, 196, 67, 0.1); border: 1px solid rgba(255, 196, 67, 0.3); padding: var(--spacing-md); display: flex; align-items: center; gap: var(--spacing-md);">
    <svg width="24" height="24" fill="none" stroke="#FFC443" stroke-width="2">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/>
        <line x1="12" y1="17" x2="12.01" y2="17"/>
    </svg>
    <p style="font-size: 0.875rem; color: var(--color-text-secondary); margin: 0;">
        <strong>Advertència:</strong> Mensaje de advertencia
    </p>
</div>
```

### Alert de Éxito
```html
<div class="card-glass" style="background: rgba(166, 238, 54, 0.1); border: 1px solid rgba(166, 238, 54, 0.3); padding: var(--spacing-md); display: flex; align-items: center; gap: var(--spacing-md);">
    <svg width="24" height="24" fill="none" stroke="var(--color-accent-primary)" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
        <polyline points="22 4 12 14.01 9 11.01"/>
    </svg>
    <p style="font-size: 0.875rem; color: var(--color-text-secondary); margin: 0;">
        <strong>Èxit!</strong> Operación completada
    </p>
</div>
```

## 🪟 Modales

### Modal Básico
```html
<div id="myModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center; z-index: 1000;">
    <div class="container" style="max-width: 400px;">
        <h3 style="margin-bottom: var(--spacing-lg); text-align: center;">Título del Modal</h3>
        <p style="color: var(--color-text-secondary); text-align: center; margin-bottom: var(--spacing-xl);">
            Contenido del modal
        </p>
        <div style="display: flex; gap: var(--spacing-md);">
            <button onclick="closeModal()" class="btn btn-secondary" style="flex: 1;">
                Cancel·lar
            </button>
            <button onclick="confirmAction()" class="btn btn-primary" style="flex: 1;">
                Confirmar
            </button>
        </div>
    </div>
</div>

<script>
function closeModal() {
    document.getElementById('myModal').style.display = 'none';
}

function openModal() {
    document.getElementById('myModal').style.display = 'flex';
}

function confirmAction() {
    // Tu acción aquí
    closeModal();
}
</script>
```

## 📑 Listas

### Lista con Items
```html
<div style="display: grid; gap: var(--spacing-lg);">
    <div class="card-glass" style="padding: var(--spacing-lg);">
        <div style="display: flex; align-items: center; gap: var(--spacing-md);">
            <div class="icon-container icon-container-sm" style="background: linear-gradient(135deg, var(--color-accent-primary) 0%, var(--color-accent-secondary) 100%);">
                <svg width="20" height="20" fill="none" stroke="white" stroke-width="2">
                    <!-- Icono -->
                </svg>
            </div>
            <div style="flex: 1;">
                <h4 style="font-size: 1rem; margin: 0 0 var(--spacing-xs) 0;">Título del Item</h4>
                <p style="font-size: 0.875rem; color: var(--color-text-secondary); margin: 0;">
                    Descripción
                </p>
            </div>
            <span class="badge badge-success">Actiu</span>
        </div>
    </div>
</div>
```

## 🎨 Iconos Comunes (SVG)

### Icono de Usuario
```html
<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
    <circle cx="12" cy="7" r="4"/>
</svg>
```

### Icono de Vehículo
```html
<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/>
    <circle cx="7" cy="17" r="2"/>
    <path d="M9 17h6"/>
    <circle cx="17" cy="17" r="2"/>
</svg>
```

### Icono de Reloj
```html
<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
    <circle cx="12" cy="12" r="10"/>
    <polyline points="12 6 12 12 16 14"/>
</svg>
```

### Icono de Ubicación
```html
<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
    <circle cx="12" cy="10" r="3"/>
</svg>
```

### Icono de Flecha Atrás
```html
<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
    <path d="M19 12H5M12 19l-7-7 7-7"/>
</svg>
```

### Icono de Check
```html
<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
    <polyline points="20 6 9 17 4 12"/>
</svg>
```

### Icono de X (Cerrar)
```html
<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
    <line x1="18" y1="6" x2="6" y2="18"/>
    <line x1="6" y1="6" x2="18" y2="18"/>
</svg>
```

## 🎯 Secciones

### Sección con Título
```html
<div class="section">
    <h3 class="section-title">Título de la Sección</h3>
    <div class="card-glass">
        <!-- Contenido -->
    </div>
</div>
```

### Divisor
```html
<div class="divider"></div>
```

## 💫 Animaciones

### Fade In (automático)
```html
<div class="fade-in">
    <!-- Contenido con animación fade-in -->
</div>
```

### Slide In
```html
<div class="slide-in">
    <!-- Contenido con animación slide-in -->
</div>
```

## 📱 Responsive

### Ocultar en móvil
```html
<div style="display: none;">
    @media (min-width: 768px) {
        display: block;
    }
</div>
```

### Cambiar disposición
```html
<div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
    <!-- En móvil: columna -->
</div>

@media (min-width: 768px) {
    <div style="flex-direction: row;">
        <!-- En desktop: fila -->
    </div>
}
```

---

**Nota**: Todos estos componentes usan las variables CSS definidas en `main.css` para mantener consistencia en el diseño.
