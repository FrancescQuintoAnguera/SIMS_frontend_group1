# Guía de Migración MySQLi a PDO

## Cambios Realizados

### 1. Archivos Core Migrados
- ✅ `DatabaseMariaDB.php` - Conexión principal migrada a PDO
- ✅ `TenantAwareDatabase.php` - Wrapper multi-tenant migrado
- ✅ `TenantManager.php` - Gestión de tenants migrada

### 2. Patrones de Migración

#### MySQLi → PDO: Preparación y Ejecución

**ANTES (MySQLi):**
```php
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
```

**DESPUÉS (PDO):**
```php
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
```

#### MySQLi → PDO: Insert ID

**ANTES (MySQLi):**
```php
$conn->insert_id
```

**DESPUÉS (PDO):**
```php
$conn->lastInsertId()
```

#### MySQLi → PDO: Affected Rows

**ANTES (MySQLi):**
```php
$conn->affected_rows
$stmt->affected_rows
```

**DESPUÉS (PDO):**
```php
$stmt->rowCount()
```

#### MySQLi → PDO: Fetch All

**ANTES (MySQLi):**
```php
$result = $stmt->get_result();
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
```

**DESPUÉS (PDO):**
```php
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

#### MySQLi → PDO: Single Row

**ANTES (MySQLi):**
```php
$result = $stmt->get_result();
$row = $result->fetch_assoc();
```

**DESPUÉS (PDO):**
```php
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
```

#### MySQLi → PDO: Transacciones

**ANTES (MySQLi):**
```php
$conn->begin_transaction();
$conn->commit();
$conn->rollback();
```

**DESPUÉS (PDO):**
```php
$conn->beginTransaction();
$conn->commit();
$conn->rollback();
```

#### MySQLi → PDO: Escape String

**ANTES (MySQLi):**
```php
$conn->real_escape_string($string)
```

**DESPUÉS (PDO):**
```php
$conn->quote($string)  // Pero mejor usar prepared statements
```

#### MySQLi → PDO: Query sin parámetros

**ANTES (MySQLi):**
```php
$result = $conn->query("SELECT * FROM users");
while ($row = $result->fetch_assoc()) {
    // proceso
}
```

**DESPUÉS (PDO):**
```php
$stmt = $conn->query("SELECT * FROM users");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // proceso
}
```

#### MySQLi → PDO: Verificar si hay resultados

**ANTES (MySQLi):**
```php
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // hay resultados
}
```

**DESPUÉS (PDO):**
```php
$stmt->execute();
if ($stmt->rowCount() > 0) {
    // hay resultados
}
// O mejor:
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row !== false) {
    // hay resultado
}
```

### 3. Archivos Pendientes de Migración

Los siguientes archivos necesitan ser actualizados manualmente siguiendo los patrones anteriores:

#### Modelos:
- `public_html/php/models/User.php`
- `public_html/php/models/Vehicle.php`
- `public_html/php/models/Booking.php`

#### APIs de Usuario:
- `public_html/php/api/*.php` (múltiples archivos)

#### Admin:
- `public_html/php/admin/*.php`

#### Auth:
- `public_html/php/auth/*.php`

#### Superadmin:
- `public_html/api/superadmin/*.php`

### 4. Verificación

Para verificar que un archivo está completamente migrado:

```bash
# Buscar usos de MySQLi
grep -n "bind_param\|get_result\|fetch_assoc\|->insert_id\|->affected_rows\|real_escape_string\|begin_transaction" archivo.php
```

Si no hay resultados, el archivo está migrado correctamente.

### 5. Testing

Después de migrar, verificar:
1. Login de usuarios
2. Registro de usuarios
3. Operaciones CRUD en vehículos
4. Sistema de reservas
5. Panel de administración
6. Multi-tenant (si aplica)

### 6. Ventajas de PDO

- ✅ Soporte para múltiples bases de datos
- ✅ Sintaxis más limpia y concisa
- ✅ Mejor manejo de errores con excepciones
- ✅ Prepared statements nativos más seguros
- ✅ Fetch modes flexibles
- ✅ Named parameters (`:param`) además de posicionales (`?`)

## Script de Migración Automática

Para ayudar con la migración, puedes usar este script:

```bash
cd /Users/ganso/Desktop/EazyRide_edit
find public_html -name "*.php" -type f -exec grep -l "bind_param\|get_result" {} \;
```

Esto listará todos los archivos que aún usan MySQLi.
