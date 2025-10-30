# ⚡ Guía Rápida - Nuevo Estilo VoltiaCar

## 🎨 Cómo Usar el Nuevo Tema Oscuro

### Colores Principales

#### Fondos
```css
/* En tu HTML/CSS usa estas clases: */
.bg-dark           → #0D2136  (Fondo principal)
.bg-dark-secondary → #1D3854  (Cards, secciones)
```

#### Botones
```html
<!-- Botón primario (verde) -->
<button class="btn-primary">Acción Principal</button>

<!-- Botón secundario (oscuro con texto azul) -->
<button class="btn-secondary">Acción Secundaria</button>

<!-- Botón outline (borde verde) -->
<button class="btn-outline">Ver más</button>
```

#### Texto
```css
.text-white        → #FFFFFF  (Texto principal)
.text-primary-blue → #69B7F0  (Links, info)
.text-primary-green → #A6EE36 (Destacados)
```

---

## 📝 Ejemplos de Uso

### 1. Card Oscuro
```html
<div class="card">
  <h2 class="card-title">Título</h2>
  <p>Contenido de la card con fondo oscuro automático</p>
  <button class="btn-primary">Acción</button>
</div>
```

### 2. Formulario
```html
<div class="form-group">
  <label class="form-label">Nombre</label>
  <input type="text" class="form-input" placeholder="Escribe aquí">
</div>
```

### 3. Modal
```html
<div class="modal-overlay active">
  <div class="modal">
    <div class="modal-header">
      <h3>Título del Modal</h3>
    </div>
    <div class="modal-body">
      <p>Contenido...</p>
    </div>
    <div class="modal-footer">
      <button class="btn-outline">Cancelar</button>
      <button class="btn-primary">Confirmar</button>
    </div>
  </div>
</div>
```

### 4. Spinner de Carga
```html
<div class="spinner"></div>
```

### 5. Alert
```html
<div class="alert alert-success">
  ✅ Operación exitosa
</div>

<div class="alert alert-error">
  ❌ Error en la operación
</div>
```

---

## 🎯 Paleta Rápida

### Para Copiar y Pegar

```css
/* Fondos */
#0D2136  /* Principal */
#1D3854  /* Secundario */
#3B3F48  /* Header */
#132B43  /* Inputs */
#2E4A68  /* Cards oscuros */

/* Acentos */
#A6EE36  /* Verde principal */
#95dd25  /* Verde hover */
#69B7F0  /* Azul claro */
#03A0FF  /* Azul oscuro */

/* Neutral */
#FFFFFF  /* Blanco */
#445a72  /* Gris claro */
```

---

## 🔧 Variables CSS Disponibles

```css
/* Usa estas variables en tu CSS: */
var(--color-dark-bg)
var(--color-dark-bg-secondary)
var(--color-dark-header)
var(--color-accent-green)
var(--color-accent-green-hover)
var(--color-accent-blue)
var(--color-dark-input)
var(--color-dark-card)
var(--color-white)
```

### Ejemplo:
```css
.mi-componente {
  background-color: var(--color-dark-bg-secondary);
  color: var(--color-white);
  border: 1px solid var(--color-accent-green);
}
```

---

## ✨ Animaciones Listas para Usar

### Fade In
```html
<div class="fade-in">Este elemento aparece gradualmente</div>
```

### Spinner
```html
<div class="spinner"></div>
```

### Hover en botones
```css
/* Ya está aplicado automáticamente en .btn-primary */
transform: scale(1.05);  /* En hover */
transform: scale(0.98);  /* En active/click */
```

---

## 📱 Responsive

El tema oscuro es completamente responsive. No necesitas cambios adicionales.

```css
/* Mobile */
@media (max-width: 640px) { ... }

/* Tablet */
@media (max-width: 768px) { ... }

/* Desktop */
@media (min-width: 1024px) { ... }
```

---

## 🎨 Estados Interactivos

### Focus (inputs)
```css
/* Automático: borde verde con scale */
input:focus {
  outline: 2px solid #A6EE36;
  transform: scale(1.02);
}
```

### Hover (buttons)
```css
/* Automático: scale y cambio de color */
.btn-primary:hover {
  background-color: #95dd25;
  transform: scale(1.05);
}
```

### Active (buttons)
```css
/* Automático: scale reducido */
.btn-primary:active {
  transform: scale(0.98);
}
```

---

## 🚀 Tips de Diseño

### ✅ Hacer:
- Usa `bg-dark` o `bg-dark-secondary` para fondos
- Usa texto blanco por defecto
- Usa `btn-primary` para acciones principales
- Usa `card` para agrupar contenido
- Mantén el contraste alto

### ❌ Evitar:
- No uses fondos claros (ya no encajan)
- No uses texto oscuro sobre fondo oscuro
- No uses azul `#1565C0` (ya no es primario)
- No uses transitions muy largas (> 0.3s)

---

## 🔍 Debugging

Si algo no se ve bien:

1. **Verifica que los CSS estén cargados:**
```html
<link rel="stylesheet" href="/css/main.css">
<link rel="stylesheet" href="/css/custom.css">
```

2. **Verifica las clases:**
```html
<!-- Correcto ✅ -->
<button class="btn-primary">Click</button>

<!-- Incorrecto ❌ -->
<button class="btn btn-primary">Click</button>
```

3. **Verifica el cache del navegador:**
```
Ctrl + Shift + R (Windows)
Cmd + Shift + R (Mac)
```

---

## 📞 Referencia Completa

Para más información, consulta:
- `CAMBIO_ESTILO.md` - Detalles completos
- `ESTILO_ANTES_DESPUES.md` - Comparación
- `ARCHIVOS_MODIFICADOS.md` - Lista de cambios

---

## 💡 Ejemplos Comunes

### Página de Login
```html
<div class="bg-dark min-h-screen flex items-center justify-center">
  <div class="card max-w-md w-full">
    <h2 class="card-title">Iniciar Sesión</h2>
    <form>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" class="form-input">
      </div>
      <button class="btn-primary w-full">Entrar</button>
    </form>
  </div>
</div>
```

### Dashboard Card
```html
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Estadísticas</h3>
  </div>
  <div class="card-body">
    <p class="text-white">Total de vehículos: 45</p>
    <p class="text-primary-blue">Disponibles: 23</p>
  </div>
</div>
```

---

**Última actualización:** 2025-10-22  
**Versión del tema:** 2.0 Dark
