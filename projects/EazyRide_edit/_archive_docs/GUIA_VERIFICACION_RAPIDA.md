# ✅ Guía de Verificación Rápida - EazyRide

## 🎯 Checklist de Pruebas

### 1️⃣ **Compra de Puntos** (purchase-time.html)

```bash
# URL: http://localhost:8080/pages/vehicle/purchase-time.html
```

**Verificar:**
- [ ] Se muestra el saldo real del usuario (no 0)
- [ ] El dropdown muestra tu nombre de usuario (no "Cargando...")
- [ ] Si eres premium, aparece "✨ Ets Premium!" con fecha
- [ ] Los precios son los nuevos: 5,99€ / 12,99€ / 22,99€ / 49,99€
- [ ] Al hacer clic en un paquete se abre el modal correctamente
- [ ] Al confirmar compra, los puntos se añaden automáticamente
- [ ] Aparece mensaje de éxito
- [ ] NO aparece error "currentLang is not defined"
- [ ] NO aparece error "pointsSelected before initialization"

**Probar:**
1. Compra el paquete básico (5,99€ - 400 pts)
2. Verifica que tu saldo aumenta
3. Recarga la página y verifica que el saldo persiste

---

### 2️⃣ **Sistema Premium** (premium.html)

```bash
# URL: http://localhost:8080/pages/profile/premium.html
```

**Verificar:**
- [ ] Dropdown muestra tu nombre de usuario
- [ ] Precios son: 7,99€/mes y 75,99€/año
- [ ] Los planes son seleccionables (cambian de estilo)
- [ ] Modal de confirmación muestra info correcta
- [ ] Si ya eres premium:
  - [ ] Aparece banner verde "Ja ets usuari Premium!"
  - [ ] Muestra fecha de expiración
  - [ ] Muestra días restantes
  - [ ] Planes ocultos o botón desactivado

**Probar (si NO eres premium):**
1. Selecciona plan mensual
2. Haz clic en "Activar Premium"
3. Confirma en el modal
4. Verifica que aparece mensaje de éxito
5. Verifica que recibes 200 puntos de bonus
6. Recarga y verifica que el banner premium aparece

---

### 3️⃣ **Perfil de Usuario** (perfil.html)

```bash
# URL: http://localhost:8080/pages/profile/perfil.html
```

**Verificar:**
- [ ] NO aparece "Cargando..." indefinidamente
- [ ] Muestra tu username correctamente
- [ ] Puntos se muestran correctamente (3800 en tu caso)
- [ ] Tiempo disponible correcto (4h 26min en tu caso)
- [ ] "Es Premium" muestra ⭐ Sí si tienes premium
- [ ] Datos personales muestran "No definit" si no están completados

**Campos esperados:**
- Username: ✅ (Karchopo)
- Fullname, DNI, Phone, etc: "No definit" (normal si no se han completado)
- Puntos: Tu saldo actual
- Tiempo: Calculado desde puntos
- Premium: Estado actual

---

### 4️⃣ **Localizar Vehículos** (localitzar-vehicle.html)

```bash
# URL: http://localhost:8080/pages/vehicle/localitzar-vehicle.html
```

**Verificar Desktop (>1024px):**
- [ ] Sidebar a la IZQUIERDA con:
  - [ ] Filtros arriba
  - [ ] Lista de vehículos abajo (con scroll)
- [ ] Mapa grande a la DERECHA
- [ ] Dropdown de usuario muestra tu nombre
- [ ] Selector de idiomas funciona

**Verificar Móvil (<768px):**
- [ ] Mapa aparece PRIMERO (arriba)
- [ ] Filtros después del mapa
- [ ] Lista de vehículos al final
- [ ] Todo en una columna
- [ ] Scroll funciona en la lista

**Probar:**
1. Haz clic en un vehículo de la lista
2. El mapa se centra en ese vehículo
3. La card del vehículo se marca como seleccionada
4. Botón de refrescar actualiza la lista
5. Filtros muestran mensaje toast

---

### 5️⃣ **Gestión** (gestio.html)

```bash
# URL: http://localhost:8080/pages/dashboard/gestio.html
```

**Verificar:**
- [ ] Dropdown muestra tu nombre (no "Usuario" genérico)
- [ ] Avatar tiene tu inicial
- [ ] Selector de idiomas funciona
- [ ] Todos los links del dropdown funcionan

---

### 6️⃣ **Toast Notifications** (TODAS las páginas)

**Verificar:**
- [ ] NO aparece error en consola: "Cannot read properties of null (reading 'appendChild')"
- [ ] Los toasts aparecen correctamente
- [ ] Animación de entrada/salida suave
- [ ] Se pueden cerrar manualmente
- [ ] Desaparecen automáticamente

---

## 🔍 Errores a NO Ver en Consola

### ❌ Errores que DEBEN estar resueltos:

1. ~~`Uncaught ReferenceError: Cannot access 'pointsSelected' before initialization`~~
2. ~~`Uncaught ReferenceError: currentLang is not defined`~~
3. ~~`Cannot read properties of null (reading 'appendChild')`~~
4. ~~`Data truncated for column 'type' at row 1`~~
5. ~~`Unknown column 'last_daily_bonus' in 'SET'`~~

### ✅ Consola limpia esperada:

```javascript
✅ Puntos recibidos: {success: true, points: 3800, ...}
✅ Saldo actualizado a: 3800
✅ Banner Premium activo mostrat (si eres premium)
✅ Dropdown de perfil inicializado
```

---

## 🧪 Pruebas de Flujo Completo

### Flujo 1: Nuevo Usuario → Compra → Premium

1. **Login** como nuevo usuario
2. **Purchase-time**: Verificar saldo en 0
3. **Comprar**: Paquete básico (5,99€)
4. **Verificar**: Saldo ahora 400 pts
5. **Premium**: Activar plan mensual (7,99€)
6. **Verificar**: 
   - Premium activo ✓
   - 200 pts bonus añadidos (total 600 pts)
   - Banner "Ets Premium" visible
7. **Purchase-time**: Verificar descuento 15% aplicado
8. **Comprar**: Otro paquete con descuento
9. **Perfil**: Ver estadísticas actualizadas

### Flujo 2: Usuario Existente con Premium

1. **Login** con tu usuario (Karchopo)
2. **Verificar** en todas las páginas:
   - Nombre aparece en dropdown
   - Avatar con inicial "K"
3. **Premium**: Ver banner activo y fecha
4. **Purchase-time**: Ver descuentos aplicados
5. **Intentar** activar premium → Botón desactivado ✓

### Flujo 3: Responsive - Móvil

1. **Abrir DevTools** (F12)
2. **Toggle** Device Toolbar (Ctrl+Shift+M)
3. **Seleccionar** iPhone 12 Pro
4. **Navegar** a localitzar-vehicle.html
5. **Verificar** layout móvil:
   - Mapa arriba
   - Filtros/Lista abajo
   - Una columna
6. **Probar** en diferentes tamaños:
   - 375px (móvil pequeño)
   - 768px (tablet)
   - 1024px (desktop)

---

## 📊 Valores Esperados

### Para usuario "Karchopo" (según test):

```json
Puntos: 3800 pts
Tiempo: 4h 26min (266 minutos)
Premium: Sí ⭐
Expira: 2025-11-22
Total Comprado: 3600 pts
Total Gastado: 0 pts
Username: Karchopo
```

### Precios Esperados:

```
Paquetes Normales:
├─ Básico: 5,99€ (400 pts)
├─ Estándar: 12,99€ (1000 pts)
├─ Plus: 22,99€ (2000 pts)
└─ Extra: 49,99€ (5000 pts)

Con Premium (-15%):
├─ Básico: 5,09€
├─ Estándar: 11,04€
├─ Plus: 19,54€
└─ Extra: 42,49€

Premium:
├─ Mensual: 7,99€
└─ Anual: 75,99€ (6,33€/mes)
```

---

## 🚨 Si Algo No Funciona

### Error de Sesión:
```bash
# Verificar que la sesión esté activa
curl -b cookies.txt http://localhost:8080/php/api/session-check.php
```

### Error de Base de Datos:
```bash
# Verificar tablas
mysql -u root -p eazyride -e "SHOW TABLES;"

# Verificar columnas de users
mysql -u root -p eazyride -e "DESCRIBE users;"
```

### Error de Premium:
```bash
# Ejecutar si hay errores de columnas
mysql -u root -p eazyride < update-premium-system.sql
```

### Limpiar Caché:
1. **Chrome**: Ctrl+Shift+Delete
2. **Hard Reload**: Ctrl+Shift+R
3. **Clear Storage**: DevTools > Application > Clear Site Data

---

## ✅ Checklist Final

- [ ] Todos los precios actualizados
- [ ] Dropdowns de usuario funcionan en TODAS las páginas
- [ ] Sistema de compra funciona (puntos se añaden)
- [ ] Sistema premium funciona (se activa correctamente)
- [ ] Perfil muestra información correcta
- [ ] Localizar vehículos responsive
- [ ] Sin errores en consola
- [ ] Toast notifications funcionan
- [ ] Selector de idiomas en todas las páginas
- [ ] Logout funciona desde cualquier página

---

**Si todo está ✅, el proyecto está listo para usar! 🚀**

**Tiempo estimado de pruebas**: 15-20 minutos
**Páginas a probar**: 5 principales
**Usuarios de prueba**: Karchopo (con premium), Nuevo usuario (sin premium)
