# 🚀 GUÍA DE INICIO RÁPIDO - EazyRide v2.0

## ✅ MEJORAS IMPLEMENTADAS

### 1. Sistema Multi-Idioma 🌍
- **3 idiomas**: Català, Español, English
- **Selector** en TODAS las páginas (esquina superior derecha)
- **Persistente**: El idioma seleccionado se guarda automáticamente
- **Traducciones**: Toda la interfaz se traduce al cambiar el idioma

### 2. Notificaciones Toast 🔔
- **Tipo moderno**: Aparecen en la esquina superior derecha
- **No molestas**: NO son notificaciones del navegador
- **Auto-cierre**: Desaparecen automáticamente después de unos segundos
- **4 tipos**: ✓ Success, ✕ Error, ⚠ Warning, ℹ Info

### 3. Botón de Perfil Global 👤
- **Accesible** desde todas las páginas
- **En el header**: Junto al selector de idioma
- **Icono de usuario**: Fácil de identificar

### 4. Sistema EazyPoints 💎

#### ¿Qué son los EazyPoints?
Moneda virtual para alquilar vehículos. Compras puntos y los usas para tiempo de alquiler.

#### Paquetes Disponibles:
```
┌────────┬─────────┬─────────┬───────────┬─────────────┐
│ Paquet │ Puntos  │ Precio  │ Descuento │ Tiempo ~    │
├────────┼─────────┼─────────┼───────────┼─────────────┤
│ Bàsic  │ 400     │ 7,50€   │ -20%      │ 30 minutos  │
│ Mig    │ 1.000   │ 18,00€  │ -23%      │ 1h 15min    │
│ Gran   │ 2.000   │ 34,00€  │ -30%      │ 2h 30min    │
│ Extra  │ 5.000   │ 80,00€  │ -35%      │ 6h+ bonus   │
└────────┴─────────┴─────────┴───────────┴─────────────┘
```

#### Conversión Puntos → Tiempo:
- **30 minutos** = 400 puntos
- **1 hora** = 800 puntos
- **2 horas** = 1.600 puntos
- **Cada hora adicional** (después de 2h) = 1.000 puntos

### 5. Sistema Premium ⭐

#### Planes:
- **Mensual**: 9,99€/mes
- **Anual**: 95€/año (ahorras 25€, ~7,92€/mes)

#### Ventajas:
1. ✅ **15% descuento adicional** en TODOS los paquetes
2. ✅ **15 minutos gratis al día** (200 puntos bonus)
3. ✅ **Menos puntos** por hora extra (900 en vez de 1.000)
4. ✅ **Acceso prioritario** a vehículos
5. ✅ **Vehículos exclusivos** Premium
6. ✅ **Soporte prioritario**
7. ✅ **Estadísticas avanzadas**

#### Ejemplo de Ahorro Premium:
```
Paquet Mig (1.000 puntos):
- Normal: 18,00€
- Premium: 15,30€ ← ¡Ahorras 2,70€!

Paquet Gran (2.000 puntos):
- Normal: 34,00€
- Premium: 28,90€ ← ¡Ahorras 5,10€!
```

## 📋 CÓMO USAR EL SISTEMA

### Para Comprar Puntos:
1. Ve a **"Comprar Punts"** desde el perfil o el menú
2. Elige el paquete que desees
3. Haz clic en el paquete para abrir confirmación
4. Confirma la compra
5. ✅ Los puntos se añaden automáticamente a tu saldo

### Para Ver Tu Saldo:
1. Ve a **"Perfil"** (botón en el header)
2. En la parte superior verás:
   - **Puntos actuales**
   - **Tiempo disponible** (conversión automática)
3. También puedes ver tu saldo en el header (si está implementado)

### Para Activar Premium:
1. Ve a **"Premium"** desde el perfil
2. Elige el plan (Mensual o Anual)
3. Haz clic en **"Activar Subscripció Premium"**
4. ✅ Recibes 200 puntos de bienvenida
5. Automáticamente ves los precios con descuento

### Para Cambiar Idioma:
1. Haz clic en el botón de idioma (esquina superior derecha)
   - Muestra: **CA**, **ES** o **EN**
2. Selecciona el idioma deseado
3. ✅ La página se traduce automáticamente
4. El idioma se guarda para tu próxima visita

## 🔧 INSTALACIÓN (Solo Primera Vez)

### Paso 1: Instalar EazyPoints
```bash
# Opción A: Desde el navegador
http://localhost:8080/setup-eazypoints.html

# Opción B: Manualmente
mysql -u root -p nombre_bd < install-complete-system.sql
```

### Paso 2: Instalar Sistema Premium
```bash
mysql -u root -p nombre_bd < install-premium-complete.sql
```

### Paso 3: Verificar
- Accede al perfil
- Deberías ver el saldo de puntos (inicialmente 0)
- Si ves "Sistema no instalado", repite los pasos anteriores

## 🎯 PÁGINAS PRINCIPALES

### 🏠 Dashboard (`/pages/dashboard/gestio.html`)
- Vista general del sistema
- Estadísticas rápidas
- Accesos directos

### 👤 Perfil (`/pages/profile/perfil.html`)
- Datos personales
- Saldo de puntos
- Tiempo disponible
- Estado Premium (si lo tienes)
- Editar información

### ⭐ Premium (`/pages/profile/premium.html`)
- Ver planes disponibles
- Activar subscripción
- Ver ventajas
- Estado actual de tu subscripción

### 💎 Comprar Puntos (`/pages/vehicle/purchase-time.html`)
- Ver todos los paquetes
- Saldo actual
- Comprar puntos
- Ver descuentos Premium (si aplica)

### 🚗 Localizar Vehículos (`/pages/vehicle/localitzar-vehicle.html`)
- Ver vehículos disponibles
- Reservar vehículos
- Usar tus puntos

### 📜 Historial (`/pages/profile/historial.html`)
- Ver alquileres pasados
- Historial de compras
- Transacciones

## ⚠️ SOLUCIÓN DE PROBLEMAS

### "Error: Unexpected token '<', is not valid JSON"
**Solución:** Instala el sistema EazyPoints (ver paso 1 arriba)

### "Columna is_premium no existe"
**Solución:** Ejecuta `install-premium-complete.sql`

### "Los puntos no se actualizan"
**Solución:** Refresca la página (F5) o cierra sesión y vuelve a entrar

### "No veo el selector de idioma"
**Solución:** Refresca la página con Ctrl+Shift+R (fuerza recarga)

### "El idioma no se guarda"
**Solución:** 
- Verifica que las cookies estén habilitadas
- Asegúrate de que localStorage funcione
- Prueba en modo incógnito

### "No puedo activar Premium"
**Solución:**
1. Verifica que ejecutaste `install-premium-complete.sql`
2. Abre la consola del navegador (F12) y busca errores
3. Verifica que la sesión esté activa

## 📱 NAVEGACIÓN

### Menú Principal:
- **Dashboard** → Vista general
- **Vehicles** → Localizar y reservar
- **Historial** → Ver actividad
- **Perfil** → Gestionar cuenta

### Desde el Perfil:
- **Premium** → Activar subscripción
- **Comprar Punts** → Añadir saldo
- **Completar Perfil** → Añadir más datos
- **Verificar Carnet** → Validar licencia
- **Històric Viatges** → Ver alquileres
- **Pagaments** → Métodos de pago

## 💡 CONSEJOS

1. **Compra el Pack Extra si usas mucho** → Mejor descuento (-35%)
2. **Hazte Premium si usas regularmente** → Ahorras 15% en cada compra
3. **Reclama tu bonus diario Premium** → 15 minutos gratis cada día
4. **Plan Anual Premium** → Ahorras 25€ vs mensual
5. **Cambia idioma según preferencia** → Se guarda automáticamente

## 🎨 CARACTERÍSTICAS VISUALES

- ✨ Diseño moderno tipo macOS
- 🎭 Efectos glassmorphism
- 🌈 Gradientes elegantes
- 🎯 Animaciones suaves
- 📱 100% Responsive
- 🌙 Tema oscuro por defecto
- ⚡ Feedback visual inmediato

## 📊 ESTADÍSTICAS EN TIEMPO REAL

En tu perfil verás:
- **Puntos actuales** (número grande con efecto degradado)
- **Tiempo disponible** (conversión automática a horas y minutos)
- **Estado Premium** (si eres Premium, con fecha de expiración)
- **Días restantes** (de tu subscripción Premium)

## 🔐 SEGURIDAD

- ✅ Sesiones seguras con PHP
- ✅ Validaciones en servidor
- ✅ Protección contra inyección SQL
- ✅ Headers de seguridad
- ✅ Cookies HTTPOnly
- ✅ Credenciales incluidas en fetch

## 🆘 SOPORTE

Si tienes problemas:
1. Revisa la consola del navegador (F12)
2. Verifica que las tablas estén creadas
3. Comprueba la configuración de PHP
4. Revisa los logs del servidor
5. Consulta `RESUMEN_MEJORAS_COMPLETO.md` para más detalles

---

## 📞 CONTACTO

**Email:** info@eazyride.com  
**Teléfono:** +34 900 123 456  
**Ubicación:** Barcelona, España

---

**¡Disfruta de EazyRide! 🚗💨**

**Versión:** 2.0  
**Última actualización:** 22 Octubre 2025  
**Estado:** ✅ Funcional y probado
