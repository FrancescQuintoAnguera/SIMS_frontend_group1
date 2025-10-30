# 🎨 Cambio de Estilo - VoltiaCar

## Resumen
Se ha actualizado el diseño de VoltiaCar del tema claro azul original a un **tema oscuro moderno** inspirado en el proyecto SIMS (Eazy Ride).

## 🎨 Paleta de Colores Actualizada

### Colores Principales
- **Fondo Principal:** `#0D2136` (Azul oscuro profundo)
- **Fondo Secundario:** `#1D3854` (Azul oscuro medio)
- **Header:** `#3B3F48` (Gris oscuro)
- **Verde Acento:** `#A6EE36` (Verde lima brillante)
- **Verde Hover:** `#95dd25` (Verde lima oscuro)

### Colores Secundarios
- **Azul Claro:** `#69B7F0`
- **Azul Oscuro:** `#03A0FF`
- **Input Dark:** `#132B43`
- **Card Dark:** `#2E4A68`
- **Card Hover:** `#3A5B7F`

### Colores de Estado
- **Success:** `#A6EE36`
- **Error:** `#EF4444`
- **Warning:** `#F59E0B`
- **Info:** `#69B7F0`

## 📝 Cambios Realizados

### 1. **main.css**
- ✅ Importada fuente Google Fonts "Poppins"
- ✅ Variables CSS actualizadas con tema oscuro
- ✅ Fondo del body cambiado a oscuro (`#0D2136`)
- ✅ Color de texto por defecto: blanco
- ✅ Botones actualizados:
  - Primario: Verde `#A6EE36` con efecto `scale(1.05)` en hover
  - Secundario: Fondo oscuro `#132B43` con texto azul
  - Outline: Borde verde con animación
- ✅ Cards con fondo oscuro `#1D3854`
- ✅ Inputs con fondo `#132B43` y texto blanco
- ✅ Focus con borde verde `#A6EE36`
- ✅ Spinner con color verde

### 2. **custom.css**
- ✅ Variables CSS del tema oscuro
- ✅ Componentes actualizados:
  - Buttons con efecto `scale` mejorado
  - Cards con fondos oscuros
  - Badges adaptados
  - Forms con inputs oscuros
  - Alerts con fondo oscuro
  - Modal con overlay más oscuro (0.7 opacity)
  - Dropdown con fondo oscuro
  - Tabs con colores claros
  - Tooltip con fondo oscuro

### 3. **localitzar-vehicle.css**
- ✅ Loading overlay actualizado a fondo oscuro
- ✅ Spinner con color verde `#A6EE36`
- ✅ Fondo del mapa actualizado

### 4. **administrar-vehicle.css**
- ✅ Dots pagination con color verde activo
- ✅ Loading overlay con fondo oscuro semi-transparente
- ✅ Spinner con bordes verdes

### 5. **vehicle-claim-modal.css**
- ✅ Modal container con fondo `#1D3854`
- ✅ Texto blanco
- ✅ Header con border oscuro
- ✅ Info card con fondo `#132B43`
- ✅ Botón confirmar con verde `#A6EE36`
- ✅ Botón cancelar con fondo oscuro
- ✅ Charge amount en verde brillante

## 🎯 Características del Nuevo Estilo

### Tipografía
- **Fuente:** Poppins (Google Fonts)
- **Fallback:** -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif

### Interacciones
- **Hover Effects:** `transform: scale(1.05)` en botones primarios
- **Transitions:** 0.2s ease para mejor fluidez
- **Focus States:** Outline verde con `transform: scale(1.02)`

### Layout
- **Header:** 10vh
- **Main:** 80vh mínimo
- **Footer:** 10vh

### Componentes
- **Border Radius:** 0.5rem - 1rem para modernidad
- **Shadows:** Más pronunciadas para profundidad
- **Spacing:** Sistema consistente con variables CSS

## 🚀 Mejoras Implementadas

1. ✅ **Consistencia visual** - Mismo esquema de colores en toda la app
2. ✅ **Mejor contraste** - Texto blanco sobre fondos oscuros
3. ✅ **Feedback visual mejorado** - Animaciones suaves y escalado
4. ✅ **Tema moderno** - Estilo dark mode profesional
5. ✅ **Accesibilidad** - Colores con buen contraste
6. ✅ **Branding coherente** - Verde acento distintivo

## 📱 Responsive
Todos los estilos mantienen compatibilidad responsive con los breakpoints existentes.

## 🔄 Compatibilidad
Los cambios son compatibles con el HTML/JS existente. No se requieren cambios estructurales.

---

**Fecha:** 2025-10-22  
**Inspiración:** SIMS (Eazy Ride) - Dark Theme Design  
**Paleta:** Dark Blue + Lime Green
