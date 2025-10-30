# 🔍 GUÍA RÁPIDA DE VERIFICACIÓN - EazyRide

## ⚡ Verificación Rápida en 5 Minutos

### 1️⃣ Verificar que el sistema está activo

```bash
cd /Users/ganso/Desktop/EazyRide_edit
docker-compose ps
```

**Esperado**: 4 contenedores corriendo (web, mariadb, mongodb, phpmyadmin)

### 2️⃣ Acceder a la aplicación

Abrir en navegador: `http://localhost:8080`

### 3️⃣ Probar compra de puntos

1. **Login**: Usa usuario existente (ej: Karchopo)
2. **Ir a**: EazyPoints / Comprar Puntos
3. **Verificar**:
   - ✅ Ver saldo actual arriba
   - ✅ Ver paquetes con nuevos precios
   - ✅ Sin errores de toast en consola
4. **Comprar**: Seleccionar cualquier paquete
5. **Confirmar** y verificar:
   - ✅ Puntos añadidos instantáneamente
   - ✅ Saldo actualizado
   - ✅ Toast de éxito visible

### 4️⃣ Probar activación Premium

1. **Ir a**: Premium (desde menú)
2. **Verificar**: 
   - ✅ Precios nuevos (14.99€ / 139.99€)
   - ✅ Sin errores en consola
3. **Si NO eres premium**:
   - Seleccionar plan
   - Activar
   - ✅ Recibir 200 puntos
   - ✅ Ver banner "Ets Premium"
4. **Si YA eres premium**:
   - ✅ Ver estado activo
   - ✅ Ver fecha expiración
   - ✅ Botón desactivado

### 5️⃣ Verificar perfil

1. **Clic** en tu nombre (arriba derecha)
2. **Seleccionar**: El meu perfil
3. **Verificar**:
   - ✅ Datos personales visibles (no "cargando")
   - ✅ Saldo de puntos correcto
   - ✅ Tiempo disponible calculado
   - ✅ Estado premium visible

---

## 🔧 Verificación de Base de Datos

```bash
cd /Users/ganso/Desktop/EazyRide_edit
docker-compose exec -e MYSQL_PWD='rootpass123' mariadb mariadb -u root simsdb -e "
SELECT 'Usuarios:' as Info, COUNT(*) as Total FROM users UNION ALL
SELECT 'Premium:', COUNT(*) FROM users WHERE is_premium = 1 UNION ALL
SELECT 'Puntos totales:', SUM(points) FROM user_points;
"
```

---

## 🐛 Si Encuentras Problemas

### Error en consola del navegador

1. **Abrir DevTools**: F12
2. **Ver Console**: Buscar errores en rojo
3. **Errores comunes ya resueltos**:
   - ❌ `Cannot read properties of null (reading 'appendChild')` → RESUELTO
   - ❌ `currentLang is not defined` → RESUELTO
   - ❌ `Cannot access 'pointsSelected' before initialization` → RESUELTO

### Puntos no se añaden

1. **Verificar sesión**: ¿Estás logueado?
2. **Ver Network**: F12 → Network → Buscar `purchase-points.php`
3. **Verificar respuesta**: ¿`success: true`?
4. **Si falla**: Ver logs de PHP

### Premium no se activa

1. **Ver consola**: F12 → Console
2. **Verificar Network**: `subscribe-premium.php`
3. **Respuesta esperada**: `success: true`, `bonus_points: 200`
4. **Si error SQL**: Ejecutar `fix-premium-tables.sql`

---

## 📊 Comandos Útiles de Verificación

### Ver logs del contenedor web
```bash
docker-compose logs -f web --tail=50
```

### Ver logs de MariaDB
```bash
docker-compose logs -f mariadb --tail=50
```

### Reiniciar servicios
```bash
docker-compose restart
```

### Ver todas las tablas
```bash
docker-compose exec -e MYSQL_PWD='rootpass123' mariadb mariadb -u root simsdb -e "SHOW TABLES;"
```

### Ver puntos de un usuario
```bash
docker-compose exec -e MYSQL_PWD='rootpass123' mariadb mariadb -u root simsdb -e "
SELECT u.username, up.points, up.total_purchased, u.is_premium 
FROM users u 
LEFT JOIN user_points up ON u.id = up.user_id;
"
```

### Ver transacciones recientes
```bash
docker-compose exec -e MYSQL_PWD='rootpass123' mariadb mariadb -u root simsdb -e "
SELECT u.username, pt.type, pt.points, pt.price, pt.created_at 
FROM point_transactions pt
JOIN users u ON pt.user_id = u.id
ORDER BY pt.created_at DESC
LIMIT 10;
"
```

---

## ✅ Checklist de Verificación Completo

### Interfaz
- [ ] Toasts aparecen sin errores
- [ ] Dropdown de perfil con nombre de usuario
- [ ] Selector de idiomas funciona
- [ ] Sin errores en consola del navegador
- [ ] Responsive en móvil

### Compra de Puntos
- [ ] Saldo actual visible
- [ ] Paquetes con precios actualizados
- [ ] Modal de confirmación aparece
- [ ] Compra se procesa
- [ ] Puntos añadidos instantáneamente
- [ ] Saldo actualizado en tiempo real
- [ ] Toast de éxito visible

### Premium
- [ ] Precios actualizados (14.99€ / 139.99€)
- [ ] Selección de plan funciona
- [ ] Modal de confirmación aparece
- [ ] Activación sin errores
- [ ] 200 puntos de bonus recibidos
- [ ] Banner "Ets Premium" visible
- [ ] Fecha de expiración correcta
- [ ] Descuentos 15% aplicados

### Perfil
- [ ] Datos personales visibles
- [ ] No queda en "cargando"
- [ ] Saldo de puntos correcto
- [ ] Tiempo disponible calculado
- [ ] Estado premium actualizado
- [ ] Avatar con inicial

### Base de Datos
- [ ] Usuarios creados
- [ ] user_points con saldos
- [ ] premium_subscriptions activas
- [ ] point_transactions registradas
- [ ] Estructura correcta

---

## 🎯 Funcionalidades Críticas a Verificar

### 1. Flujo Completo de Compra
```
Login → Ver saldo → Seleccionar paquete → Confirmar → 
Ver puntos añadidos → Saldo actualizado → Toast éxito
```

### 2. Flujo Completo Premium
```
Login → Ir a Premium → Seleccionar plan → Activar →
Recibir 200 pts → Ver estado activo → Verificar descuentos
```

### 3. Flujo Perfil
```
Login → Clic en nombre → Ver perfil →
Verificar datos → Ver saldo → Ver premium
```

---

## 📱 URLs de Prueba

- **Inicio**: http://localhost:8080
- **Login**: http://localhost:8080/pages/auth/login.html
- **Gestión**: http://localhost:8080/pages/dashboard/gestio.html
- **Comprar Puntos**: http://localhost:8080/pages/vehicle/purchase-time.html
- **Premium**: http://localhost:8080/pages/profile/premium.html
- **Perfil**: http://localhost:8080/pages/profile/perfil.html
- **phpMyAdmin**: http://localhost:8081

---

## 🆘 Troubleshooting Rápido

| Problema | Solución |
|----------|----------|
| No puedo acceder | `docker-compose up -d` |
| Error 500 | Ver logs: `docker-compose logs web` |
| No carga página | Verificar contenedores: `docker-compose ps` |
| Base de datos vacía | Ejecutar: `fix-premium-tables.sql` |
| Sesión expirada | Hacer logout y login de nuevo |
| Puntos no se ven | Refrescar página (F5) |
| Error de toast | Limpiar caché del navegador |

---

## ✅ ¡Sistema Verificado y Listo!

Si todos los checks están ✅, el sistema está **100% funcional**.

**Última verificación**: 22 de octubre de 2025
**Estado**: ✅ OPERATIVO

---

## 📚 Más Información

- `SISTEMA_LISTO.md` - Guía completa de uso
- `REPARACIONES_FINALIZADAS.md` - Lista de cambios
- `PRECIOS_ACTUALIZADOS.md` - Estrategia de precios
- `RESUMEN_REPARACIONES.txt` - Resumen visual

---

**¿Todo funciona?** 🎉 ¡Excelente! El sistema está listo para producción.

**¿Algún problema?** 📧 Revisar la documentación o logs del servidor.
