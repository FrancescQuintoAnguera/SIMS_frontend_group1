# 🚀 INICIO RÁPIDO - SISTEMA PREMIUM EAZY RIDE

## ⚡ En 3 Pasos

### 1️⃣ Instalar el Sistema (Solo una vez)

```bash
cd /Users/ganso/Desktop/EazyRide_edit
./install-premium-system.sh
```

✅ Esto creará todas las tablas necesarias en la base de datos.

### 2️⃣ Dar Puntos de Prueba (Opcional)

```bash
docker exec -i VC-mariadb mariadb -u root -prootpass123 simsdb -e "UPDATE user_points SET points = 5000 WHERE user_id = 1;"
```

### 3️⃣ Probar el Sistema

Accede a estas URLs:

- **Perfil**: http://localhost:8080/pages/profile/perfil.html
- **Premium**: http://localhost:8080/pages/profile/premium.html
- **Comprar Puntos**: http://localhost:8080/pages/vehicle/purchase-time.html

---

## 📖 Documentación Completa

Si necesitas más información, consulta:

- `SISTEMA_INSTALADO.md` - Estado actual y verificaciones
- `GUIA_IMPLEMENTACION_PREMIUM.md` - Guía completa
- `RESUMEN_REORGANIZACION_FRONTEND.md` - Cambios visuales
- `RESUMEN_FINAL.txt` - Resumen visual

---

## 🎯 Precios del Sistema

### Paquetes de Puntos

| Paquete | Puntos | Normal | Premium (-15%) |
|---------|--------|--------|----------------|
| Bàsic   | 400    | 7,50€  | 6,38€         |
| Mig     | 1.000  | 18,00€ | 15,30€        |
| Gran    | 2.000  | 34,00€ | 28,90€        |
| Extra   | 5.000  | 80,00€ | 68,00€        |

### Suscripción Premium

| Plan    | Precio | Ahorro |
|---------|--------|--------|
| Mensual | 9,99€  | -      |
| Anual   | 95€    | 25€    |

### Beneficios Premium

✅ 15% descuento en paquetes  
✅ 15 min gratis al día (200 pts)  
✅ Reducción a 900 pts/hora (vs 1000)  
✅ Acceso prioritario  
✅ Vehículos exclusivos  
✅ Atención prioritaria  

---

## 🐛 Solución de Problemas

### Error: "is_premium column not found"
```bash
./install-premium-system.sh
```

### Error: "Sistema EazyPoints no instalado"
```bash
./install-premium-system.sh
```

### Error: Los puntos no se suman
```sql
-- Verificar que existe el registro
docker exec -i VC-mariadb mariadb -u root -prootpass123 simsdb \
  -e "SELECT * FROM user_points WHERE user_id = 1;"

-- Si no existe, crearlo
docker exec -i VC-mariadb mariadb -u root -prootpass123 simsdb \
  -e "INSERT INTO user_points (user_id, points) VALUES (1, 0);"
```

---

## ✅ Verificar Instalación

```bash
# Ver estado de las tablas
docker exec -i VC-mariadb mariadb -u root -prootpass123 simsdb -e "
SELECT 'users' as tabla, COUNT(*) as registros FROM users
UNION ALL SELECT 'user_points', COUNT(*) FROM user_points
UNION ALL SELECT 'point_transactions', COUNT(*) FROM point_transactions
UNION ALL SELECT 'premium_subscriptions', COUNT(*) FROM premium_subscriptions;
"

# Ver columnas de premium
docker exec -i VC-mariadb mariadb -u root -prootpass123 simsdb -e "
SHOW COLUMNS FROM users WHERE Field IN ('is_premium', 'premium_expires_at');
"
```

---

## 🎉 ¡Listo!

El sistema está funcional. Solo accede a las URLs y prueba todas las funciones.

**Cualquier duda**: Consulta los archivos .md en la raíz del proyecto.
