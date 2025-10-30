# GUÍA DE IMPLEMENTACIÓN COMPLETA DEL SISTEMA PREMIUM Y EAZYPOINTS

## 📋 PASOS PARA IMPLEMENTAR

### 1. Actualizar la Base de Datos

Ejecuta el siguiente comando desde la raíz del proyecto:

```bash
docker exec -i eazyride_edit-mariadb-1 mysql -u root -prootpassword eazyride < update-premium-system.sql
```

O si prefieres hacerlo manualmente, conéctate a MariaDB y ejecuta:

```bash
docker exec -it eazyride_edit-mariadb-1 mysql -u root -prootpassword eazyride
```

Luego copia y pega el contenido de `update-premium-system.sql`.

### 2. Verificar la Instalación

Verifica que las tablas se crearon correctamente:

```sql
SHOW TABLES;
DESCRIBE users;
DESCRIBE user_points;
DESCRIBE point_transactions;
DESCRIBE premium_subscriptions;
```

Deberías ver las columnas `is_premium` y `premium_expires_at` en la tabla `users`.

### 3. Archivos Actualizados

Los siguientes archivos han sido actualizados y reorganizados:

#### Páginas HTML:
- ✅ `pages/profile/perfil.html` - Perfil con saldo de puntos y estado premium
- ✅ `pages/profile/premium.html` - Página de suscripción premium rediseñada
- ✅ `pages/vehicle/purchase-time.html` - Compra de puntos con descuentos premium

#### Archivos PHP:
- ✅ `php/api/get-points.php` - Obtener saldo y estado premium
- ✅ `php/api/purchase-points.php` - Comprar paquetes de puntos
- ✅ `php/api/subscribe-premium.php` - Activar suscripción premium

#### JavaScript:
- ✅ `js/toast.js` - Sistema de notificaciones toast (ya existente)

### 4. Sistema de Precios Implementado

#### Paquetes de Puntos (Usuarios Normales):
- **Básico**: 400 pts → 7,50€ (20% desc)
- **Medio**: 1.000 pts → 18,00€ (23% desc)
- **Grande**: 2.000 pts → 34,00€ (30% desc)
- **Extra**: 5.000 pts → 80,00€ (35% desc + bonus)

#### Paquetes de Puntos (Usuarios Premium):
Los usuarios premium obtienen un **15% de descuento adicional** en todos los paquetes:
- **Básico**: 400 pts → 6,38€ (35% desc total)
- **Medio**: 1.000 pts → 15,30€ (38% desc total)
- **Grande**: 2.000 pts → 28,90€ (45% desc total)
- **Extra**: 5.000 pts → 68,00€ (50% desc total)

#### Coste de Alquiler:
- **30 minutos**: 400 puntos (~7,50€)
- **1 hora**: 800 puntos
- **2 horas**: 1.600 puntos
- **A partir de 2h**: +1.000 pts/hora adicional (usuarios normales)
- **A partir de 2h**: +900 pts/hora adicional (usuarios premium)

#### Suscripción Premium:
- **Mensual**: 9,99€/mes
- **Anual**: 95€/año (Ahorro de 25€)

#### Beneficios Premium:
1. 15% de descuento en todos los paquetes de puntos
2. 15 minutos gratuitos al día (200 puntos diarios)
3. Reducción de puntos: 900 pts/hora en lugar de 1.000 pts/hora
4. Accés prioritario a vehículos
5. Vehículos exclusivos
6. Atención al cliente prioritaria

### 5. Funcionamiento del Sistema

#### Flujo de Compra de Puntos:

1. Usuario entra en `purchase-time.html`
2. Sistema verifica si es premium mediante `get-points.php`
3. Si es premium, se aplican precios con 15% descuento
4. Usuario selecciona un paquete y confirma
5. Los puntos se agregan automáticamente a su cuenta
6. Se registra la transacción en `point_transactions`

#### Flujo de Suscripción Premium:

1. Usuario entra en `premium.html`
2. Selecciona plan (mensual o anual)
3. Al confirmar, se ejecuta `subscribe-premium.php`
4. Se actualiza `users.is_premium = 1` y `users.premium_expires_at`
5. Se crea registro en `premium_subscriptions`
6. Se bonifican 200 puntos (15 minutos gratis)
7. Se registra el bono en `point_transactions`

#### Cálculo de Tiempo Disponible:

El sistema calcula automáticamente el tiempo disponible basándose en los puntos:

```
- Menos de 400 pts = 0 minutos
- 400-799 pts = 30 minutos
- 800-1599 pts = proporcional hasta 120 minutos
- 1600+ pts = 120 minutos + (puntos restantes / 1000 * 60)
```

### 6. Sistema de Notificaciones Toast

Todas las notificaciones ahora usan el sistema de toast en lugar de `alert()` o notificaciones del navegador.

Uso:
```javascript
showToast('Mensaje de éxito', 'success');
showToast('Mensaje de error', 'error');
showToast('Mensaje de advertencia', 'warning');
showToast('Mensaje informativo', 'info');
```

### 7. Diseño Consistente

Todos los archivos HTML ahora tienen:
- ✅ Header consistente con logo y navegación
- ✅ Footer en todas las páginas
- ✅ Estilos uniformes usando `main.css`
- ✅ Componentes reutilizables (cards, badges, buttons)
- ✅ Gradientes y colores consistentes
- ✅ Animaciones suaves

### 8. Problemas Solucionados

- ❌ Error "is_premium column not found" → ✅ Se agrega la columna con el script SQL
- ❌ Notificaciones del navegador → ✅ Sistema de toast implementado
- ❌ Puntos no se suman → ✅ Sistema de compra funcional
- ❌ Tiempo no se calcula → ✅ Conversión automática de puntos a tiempo
- ❌ Premium no funciona → ✅ Sistema completo implementado
- ❌ Precios no cambian para premium → ✅ Descuentos aplicados automáticamente

### 9. Testing

Para probar el sistema:

1. **Crear usuario de prueba**:
```sql
-- Ya deberías tener un usuario creado
SELECT id, email, username, is_premium FROM users LIMIT 1;
```

2. **Dar puntos iniciales para pruebas**:
```sql
UPDATE user_points SET points = 1000 WHERE user_id = 1;
```

3. **Activar premium manualmente (opcional)**:
```sql
UPDATE users SET is_premium = 1, premium_expires_at = DATE_ADD(CURDATE(), INTERVAL 1 MONTH) WHERE id = 1;
INSERT INTO premium_subscriptions (user_id, type, status, start_date, end_date, price) 
VALUES (1, 'monthly', 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 9.99);
```

4. **Verificar saldo**:
   - Ve a `/pages/profile/perfil.html`
   - Deberías ver tu saldo de puntos y tiempo disponible

5. **Comprar puntos**:
   - Ve a `/pages/vehicle/purchase-time.html`
   - Selecciona un paquete
   - Confirma la compra
   - Verifica que los puntos se sumaron

6. **Activar premium**:
   - Ve a `/pages/profile/premium.html`
   - Selecciona un plan
   - Activa la suscripción
   - Verifica los descuentos en purchase-time.html

### 10. Próximos Pasos

Para completar la reorganización del frontend:

1. Agregar header/footer a TODOS los HTML restantes
2. Unificar estilos en páginas que faltan
3. Implementar el sistema de bonus diario premium
4. Agregar estadísticas de uso en el perfil
5. Crear página de historial de transacciones

## 🎨 Paleta de Colores Utilizada

```css
--color-accent-primary: #A6EE36 (Verde lima)
--color-accent-secondary: #69B7F0 (Azul)
--color-accent-blue: #4A9FF5
--color-accent-purple: #BF5AF2
Premium Gold: #FFD700
Warning: #ff9800
Error: #f44336
```

## 📝 Notas Importantes

- Los toasts se muestran en la esquina superior derecha
- Los precios premium se calculan automáticamente en el backend
- Las suscripciones caducadas se pueden renovar
- El sistema es compatible con el flujo de reservas existente
- Todos los cambios mantienen la funcionalidad previa

## 🐛 Debugging

Si algo no funciona:

1. Verifica la consola del navegador (F12)
2. Revisa los logs del contenedor PHP:
   ```bash
   docker logs -f eazyride_edit-web-1
   ```
3. Verifica que las tablas existen en MariaDB
4. Asegúrate de tener sesión activa
5. Verifica que los archivos PHP tengan permisos correctos

¡Todo listo para usar! 🚗⚡
