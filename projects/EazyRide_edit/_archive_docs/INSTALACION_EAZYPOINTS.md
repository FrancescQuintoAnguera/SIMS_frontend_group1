# 🚀 Instalación Sistema EazyPoints

## ✅ Archivos Creados

### 1. Base de Datos
- `eazypoints-schema.sql` - Esquema completo de tablas
- `init-eazypoints-test.sql` - Datos de prueba

### 2. APIs PHP
- `public_html/php/api/get-points.php` - Obtener saldo y tiempo
- `public_html/php/api/purchase-points.php` - Comprar paquetes

### 3. Frontend
- `public_html/pages/vehicle/purchase-time.html` - ✅ Ya actualizado
- `public_html/pages/dashboard/gestio.html` - ✅ Ya actualizado

---

## 📝 Pasos de Instalación

### Paso 1: Crear las Tablas en la Base de Datos

```bash
# Conectar a MySQL
mysql -u root -p eazyride

# O si usas el contenedor Docker
docker exec -i eazyride-mariadb mysql -u root -proot eazyride < eazypoints-schema.sql
```

O ejecuta manualmente:
```bash
mysql -u root -p eazyride < eazypoints-schema.sql
```

### Paso 2: Verificar que las Tablas se Crearon

```sql
USE eazyride;
SHOW TABLES;

-- Deberías ver:
-- user_points
-- point_transactions
-- premium_subscriptions
```

### Paso 3: (Opcional) Agregar Puntos de Prueba

Edita `init-eazypoints-test.sql` y cambia el `user_id = 1` por tu ID de usuario, luego:

```bash
mysql -u root -p eazyride < init-eazypoints-test.sql
```

### Paso 4: Verificar las APIs PHP

Las APIs ya están creadas en:
- `public_html/php/api/get-points.php`
- `public_html/php/api/purchase-points.php`

Verifica que el archivo `public_html/php/config/database.php` existe y tiene la configuración correcta.

### Paso 5: Probar el Sistema

1. **Iniciar sesión** en la aplicación
2. **Ir al dashboard** (`gestio.html`)
3. **Verificar** que aparecen:
   - EazyPoints: Tu saldo actual
   - Temps disponible: Tiempo calculado según tus puntos

4. **Ir a "Comprar EazyPoints"** (`purchase-time.html`)
5. **Seleccionar un paquete** y confirmar
6. **Verificar** que:
   - Se muestra toast de éxito
   - El saldo se actualiza automáticamente
   - El tiempo disponible se recalcula

---

## 🧪 Testing

### Test 1: Verificar Carga de Puntos
```bash
# En la consola del navegador (F12)
fetch('/php/api/get-points.php', { credentials: 'include' })
  .then(r => r.json())
  .then(console.log);

# Debería devolver:
# {
#   "success": true,
#   "points": 1000,
#   "total_purchased": 1000,
#   "total_spent": 0,
#   "minutes_available": 75,
#   "hours_available": 1.3
# }
```

### Test 2: Comprar Paquete
```bash
fetch('/php/api/purchase-points.php', {
  method: 'POST',
  credentials: 'include',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    points: 400,
    price: 7.50,
    package: 'Bàsic',
    discount: 20
  })
}).then(r => r.json()).then(console.log);

# Debería devolver:
# {
#   "success": true,
#   "message": "Compra realitzada correctament!",
#   "points_added": 400,
#   "new_balance": 1400,
#   "price_paid": 7.5,
#   "discount_applied": 20
# }
```

### Test 3: Verificar en Base de Datos
```sql
-- Ver puntos del usuario
SELECT * FROM user_points WHERE user_id = 1;

-- Ver transacciones
SELECT * FROM point_transactions WHERE user_id = 1 ORDER BY created_at DESC;
```

---

## 🔧 Conversión de Puntos a Tiempo

El sistema calcula automáticamente el tiempo disponible según esta tabla:

| Puntos | Tiempo Equivalente |
|--------|-------------------|
| 0-399 | 0 minutos |
| 400-799 | 30 minutos |
| 800-1599 | ~60 minutos |
| 1600+ | 2 horas base + tiempo adicional |

### Fórmula de Cálculo:

```javascript
if (points < 400) return 0;
if (points < 800) return 30;
if (points < 1600) return Math.floor(points / 800 * 60);

// 2 horas base + adicional
let minutes = 120;
let remaining = points - 1600;
minutes += Math.floor(remaining / 1000 * 60);
return minutes;
```

---

## 📱 Funcionalidades Implementadas

### ✅ Dashboard (gestio.html)
- Muestra saldo de EazyPoints en tiempo real
- Muestra tiempo disponible calculado
- Se actualiza automáticamente cada 30 segundos
- Card de "Comprar EazyPoints" resaltada si no hay puntos

### ✅ Compra de Puntos (purchase-time.html)
- Muestra saldo actual
- 4 paquetes con descuentos progresivos
- Banner premium
- Modal de confirmación
- Actualización automática del saldo tras compra
- Toast notifications

### ✅ APIs Backend
- **GET** `/php/api/get-points.php` - Obtener saldo y tiempo
- **POST** `/php/api/purchase-points.php` - Comprar paquete

---

## 🎯 Próximos Pasos Recomendados

### Alta Prioridad
1. ✅ Integrar pasarela de pago (Stripe/PayPal)
2. ✅ Implementar descuento premium (15%)
3. ✅ Actualizar `administrar-vehicle.html` para descontar puntos al usar vehículo

### Media Prioridad
4. ⚠️ Crear página de historial de transacciones
5. ⚠️ Implementar sistema premium completo
6. ⚠️ Agregar bonus diario para premium

### Baja Prioridad
7. 📊 Analytics de uso de puntos
8. 🎮 Gamificación (badges, rankings)
9. 📧 Notificaciones por email
10. 💰 Sistema de referidos

---

## ⚠️ Troubleshooting

### Error: "No autoritzat"
- Verificar que la sesión está iniciada
- Revisar que `session_start()` está en las APIs

### Error: "Error del servidor"
- Revisar logs de PHP: `/var/log/apache2/error.log`
- Verificar conexión a base de datos
- Comprobar que las tablas existen

### No se actualizan los puntos
- Verificar en la consola del navegador si hay errores
- Comprobar que las rutas de las APIs son correctas
- Verificar permisos de archivos PHP

### Los puntos no se convierten a tiempo
- Revisar la función `loadPointsAndTime()` en gestio.html
- Verificar que la API devuelve `minutes_available`

---

## 📊 Verificación Final

### Checklist de Instalación:

- [ ] Tablas creadas en base de datos
- [ ] APIs PHP funcionando
- [ ] Dashboard muestra puntos
- [ ] Dashboard muestra tiempo disponible
- [ ] Compra de paquetes funciona
- [ ] Saldo se actualiza automáticamente
- [ ] Toast notifications funcionan
- [ ] No hay errores en consola

---

## 🎉 ¡Sistema Listo!

Si todos los checks están marcados, el sistema EazyPoints está funcionando correctamente.

### Comandos útiles:

```bash
# Ver logs en tiempo real
tail -f /var/log/apache2/error.log

# Resetear puntos de un usuario
mysql -u root -p eazyride -e "UPDATE user_points SET points = 0 WHERE user_id = 1;"

# Ver todas las transacciones
mysql -u root -p eazyride -e "SELECT * FROM point_transactions ORDER BY created_at DESC LIMIT 10;"
```

---

**Fecha**: Octubre 2025  
**Versión**: 1.0  
**Autor**: Sistema EazyPoints
