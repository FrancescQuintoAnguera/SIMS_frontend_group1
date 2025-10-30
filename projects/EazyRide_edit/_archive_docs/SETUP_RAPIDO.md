# 🚀 Setup Rápido EazyPoints

## ⚠️ Error Encontrado

El error que estás viendo:
```
SyntaxError: Unexpected token '<', "<br />
```

Significa que las APIs están devolviendo HTML (error de PHP) en lugar de JSON.

## ✅ Solución en 3 Pasos

### Paso 1: Acceder al Setup

Abre tu navegador y ve a:
```
http://localhost/setup-eazypoints.html
```

O si usas otro puerto:
```
http://localhost:8080/setup-eazypoints.html
```

### Paso 2: Click en "Instal·lar EazyPoints"

Verás una página con un botón. Haz click y espera a que se complete.

Deberías ver:
```
✅ Sistema EazyPoints instalado correctamente
✓ Tabla user_points creada/verificada
✓ Tabla point_transactions creada/verificada
✓ Tabla premium_subscriptions creada/verificada
✓ Columnas premium agregadas a users
✓ Inicializados puntos para N usuarios
```

### Paso 3: Probar las APIs

Abre la consola del navegador (F12) y ejecuta:

```javascript
// Test 1: Ver puntos
fetch('/php/api/get-points.php', {credentials: 'include'})
  .then(r => r.json())
  .then(console.log);

// Deberías ver:
// {
//   success: true,
//   points: 0,
//   minutes_available: 0,
//   hours_available: 0
// }
```

---

## 🎯 Si Todo Funciona

Ahora puedes:

1. **Ir al dashboard** → Verás "0 pts" y "0h disponibles"
2. **Ir a "Comprar EazyPoints"** → Selecciona un paquete
3. **Confirmar** → Los puntos se suman automáticamente
4. **Volver al dashboard** → Verás los puntos actualizados

---

## 🔧 Troubleshooting

### Error: "No autoritzat"
→ Tienes que hacer login primero

### Error: "Error del servidor"
→ Verifica que las tablas se crearon correctamente:
```sql
SHOW TABLES LIKE '%point%';
```

### Las APIs no responden
→ Verifica la ruta:
- Debe ser `/php/api/get-points.php`
- NO `../../php/api/get-points.php` desde el setup

### Sigo viendo el error de JSON
1. Abre `/php/api/get-points.php` directamente en el navegador
2. ¿Ves un error de PHP? → Copia el error completo
3. ¿Ves JSON válido? → Las tablas no existen, ejecuta el setup

---

## 📝 Verificación Manual (Alternativa)

Si prefieres hacerlo manualmente por SQL:

```bash
# Conectar a la base de datos
mysql -u root -p eazyride

# Ejecutar
source /ruta/a/eazypoints-schema.sql

# Verificar
SHOW TABLES;
SELECT * FROM user_points;
```

---

## ✅ Checklist Final

- [ ] Setup ejecutado sin errores
- [ ] Tabla user_points existe
- [ ] Tabla point_transactions existe
- [ ] `/php/api/get-points.php` devuelve JSON
- [ ] `/php/api/purchase-points.php` funciona
- [ ] Dashboard muestra "0 pts"
- [ ] Compra de paquetes funciona
- [ ] Saldo se actualiza automáticamente

---

**¡Listo!** 🎉 Si todos los checks están marcados, el sistema funciona perfectamente.
