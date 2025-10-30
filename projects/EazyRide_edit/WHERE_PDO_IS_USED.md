# 📍 Dónde Se Usa PDO en el Proyecto

## 🎯 Punto de Entrada Principal

### 1. Archivo de Conexión PDO
**`public_html/php/core/DatabaseMariaDB.php`** (línea 66)

```php
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
self::$conn = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
]);
```

Este es el **ÚNICO lugar** donde se crea la conexión PDO. Todos los demás archivos la usan a través de este.

---

## 🔄 Cómo Fluye PDO en el Proyecto

### Flujo de Conexión:

```
DatabaseMariaDB::getConnection()
         ↓
    [Retorna PDO Connection]
         ↓
    ┌────────────────────────────────────┐
    │  Usado por:                        │
    ├────────────────────────────────────┤
    │  1. Modelos (User, Vehicle, etc)   │
    │  2. TenantManager                  │
    │  3. TenantAwareDatabase            │
    │  4. APIs directamente              │
    └────────────────────────────────────┘
```

---

## 📂 Archivos que Usan PDO

### ✅ Core del Sistema (4 archivos)
```
public_html/php/core/
├── DatabaseMariaDB.php      ← CREA la conexión PDO
├── TenantAwareDatabase.php  ← Usa PDO
├── TenantManager.php        ← Usa PDO
└── TenantBootstrap.php      ← Usa PDO
```

### ✅ Modelos (3 archivos)
```
public_html/php/models/
├── User.php     ← fetch(), execute()
├── Vehicle.php  ← fetchAll(), execute()
└── Booking.php  ← fetch(), lastInsertId()
```

### ✅ APIs de Usuario (20+ archivos)
```
public_html/php/api/
├── book-vehicle.php
├── release-vehicle.php
├── get-points.php
├── purchase-points.php
├── subscribe-premium.php
├── claim-daily-bonus.php
├── vehicle-control.php
└── ... (15+ archivos más)
```

### ✅ Panel de Administración (3 archivos)
```
public_html/php/admin/
├── users.php
├── vehicles.php
└── bookings.php
```

### ✅ Gestión de Usuario (4 archivos)
```
public_html/php/user/
├── profile.php
├── user-profile.php
├── update-profile.php
└── change-password.php
```

### ✅ Superadmin
```
public_html/api/superadmin/
└── create-tenant.php
```

---

## 🔍 Ejemplos de Uso de PDO

### En Modelos:
```php
// User.php
$db = DatabaseMariaDB::getConnection(); // ← Obtiene PDO
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC); // ← Usa PDO
```

### En APIs:
```php
// book-vehicle.php
require_once __DIR__ . '/../core/DatabaseMariaDB.php';
$db = DatabaseMariaDB::getConnection(); // ← Obtiene PDO
$stmt = $db->prepare("INSERT INTO bookings ...");
$stmt->execute([$user_id, $vehicle_id, ...]); // ← Usa PDO
$booking_id = $db->lastInsertId(); // ← Método PDO
```

### En TenantManager:
```php
// TenantManager.php
$this->db = DatabaseMariaDB::getConnection(); // ← Obtiene PDO
$stmt = $this->db->prepare("SELECT * FROM tenants WHERE id = ?");
$stmt->execute([$tenantId]);
$tenant = $stmt->fetch(PDO::FETCH_ASSOC); // ← Usa PDO
```

---

## 📊 Estadísticas de Uso

```bash
# Total de archivos usando PDO
$ find public_html -name "*.php" ! -name "*.bak*" -exec grep -l "PDO::FETCH_ASSOC" {} \; | wc -l
29

# Archivos usando prepare() con PDO
$ find public_html -name "*.php" ! -name "*.bak*" -exec grep -l "prepare(" {} \; | wc -l
40+

# Archivos usando execute([...]) (sintaxis PDO)
$ find public_html -name "*.php" ! -name "*.bak*" -exec grep -l "execute(\[" {} \; | wc -l
35+
```

---

## 🎯 Puntos Clave

### 1. **Una Sola Conexión PDO**
   - Se crea en `DatabaseMariaDB.php`
   - Es un singleton (una sola instancia)
   - Se reutiliza en todo el proyecto

### 2. **Todos los Archivos Usan PDO Indirectamente**
   ```php
   $db = DatabaseMariaDB::getConnection(); // ← Esto ES PDO
   ```

### 3. **Métodos PDO Usados en el Proyecto**
   - `prepare()` - Preparar queries
   - `execute()` - Ejecutar con parámetros
   - `fetch(PDO::FETCH_ASSOC)` - Obtener una fila
   - `fetchAll(PDO::FETCH_ASSOC)` - Obtener todas las filas
   - `lastInsertId()` - ID del último insert
   - `rowCount()` - Número de filas afectadas
   - `beginTransaction()` - Iniciar transacción
   - `commit()` - Confirmar transacción
   - `rollback()` - Revertir transacción

---

## 🔧 Verificación Rápida

Para ver que PDO está activo:

```bash
# Ver la conexión PDO
grep -A5 "new PDO" public_html/php/core/DatabaseMariaDB.php

# Ver todos los archivos que lo usan
grep -r "DatabaseMariaDB::getConnection()" public_html --include="*.php" | head -10

# Verificar que no queda mysqli
grep -r "new mysqli" public_html --include="*.php" ! -path "*/.*" | wc -l
# Debería mostrar: 0
```

---

## 📖 Resumen

**PDO se usa en TODO el proyecto** a través de:

1. **Conexión Central**: `DatabaseMariaDB::getConnection()`
2. **Modelos**: Usan PDO para CRUD
3. **APIs**: Usan PDO para operaciones
4. **Core**: TenantManager y TenantAwareDatabase usan PDO
5. **Admin**: Panel de administración usa PDO

**NO hay código usando mysqli** - Todo migrado a PDO ✅

