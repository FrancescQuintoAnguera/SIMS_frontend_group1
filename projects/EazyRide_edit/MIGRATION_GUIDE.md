# 📋 Guía de Migración a Multi-Tenant

## Pasos para Migrar Archivos Existentes

### Paso 1: Identificar archivos que necesitan migración

Buscar todos los archivos PHP que interactúan con la base de datos:

```bash
find public_html -name "*.php" | grep -E "(api|auth|admin|user)" > files_to_migrate.txt
```

### Paso 2: Patrón de Migración

#### ANTES (Single-Tenant)

```php
<?php
require_once '../config/database.php';
session_start();

$db = getDB();

// Consulta sin filtro de tenant
$stmt = $db->prepare("SELECT * FROM vehicles WHERE status = ?");
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();

$vehicles = [];
while ($row = $result->fetch_assoc()) {
    $vehicles[] = $row;
}

echo json_encode(['vehicles' => $vehicles]);
?>
```

#### DESPUÉS (Multi-Tenant)

```php
<?php
// 1. Añadir TenantBootstrap PRIMERO
require_once __DIR__ . '/../php/core/TenantBootstrap.php';

// 2. Usar getTenantDB() en lugar de getDB()
$db = getTenantDB();

// 3. Usar métodos tenant-aware
$result = $db->select('vehicles', '*', 'status = ?', [$status]);

$vehicles = [];
while ($row = $result->fetch_assoc()) {
    $vehicles[] = $row;
}

// 4. Incluir info del tenant en respuesta
echo json_encode([
    'success' => true,
    'tenant' => CURRENT_TENANT_NAME,
    'vehicles' => $vehicles
]);
?>
```

### Paso 3: Ejemplos de Migración por Tipo de Archivo

#### API Endpoints

**Archivo: `api/vehicles/list.php`**

```php
<?php
// Añadir al inicio
require_once __DIR__ . '/../../php/core/TenantBootstrap.php';

// Verificar autenticación y tenant
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Not authenticated']));
}

if (!verifyTenantSession()) {
    die(json_encode(['error' => 'Tenant access denied']));
}

// Usar base de datos tenant-aware
$db = getTenantDB();
$vehicles = $db->select('vehicles', '*', 'status = ?', ['available']);

// ... resto del código
?>
```

#### Autenticación

**Archivo: `auth/login.php`**

```php
<?php
require_once __DIR__ . '/../php/core/TenantBootstrap.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$db = getTenantDB();

// Buscar usuario solo en el tenant actual
$user = $db->selectOne('users', '*', 'email = ?', [$email]);

if ($user && password_verify($password, $user['password'])) {
    // Doble verificación de tenant
    if ($user['tenant_id'] == CURRENT_TENANT_ID) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['tenant_id'] = $user['tenant_id'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Tenant mismatch']);
    }
}
?>
```

#### Admin Panels

**Archivo: `admin/dashboard.php`**

```php
<?php
require_once __DIR__ . '/../php/core/TenantBootstrap.php';

// Verificar que es admin del tenant actual
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.html');
    exit;
}

$db = getTenantDB();
$user = $db->selectOne('users', '*', 'id = ?', [$_SESSION['user_id']]);

if (!$user['is_admin']) {
    die('Admin access required');
}

// Obtener estadísticas del tenant
$stats = [
    'users' => $db->count('users'),
    'vehicles' => $db->count('vehicles'),
    'bookings' => $db->count('bookings')
];
?>

<h1>Dashboard - <?php echo CURRENT_TENANT_NAME; ?></h1>
<div class="stats">
    <div>Users: <?php echo $stats['users']; ?></div>
    <div>Vehicles: <?php echo $stats['vehicles']; ?></div>
    <div>Bookings: <?php echo $stats['bookings']; ?></div>
</div>
```

### Paso 4: Casos Especiales

#### Consultas con JOINs

```php
// ANTES
$query = "
    SELECT b.*, v.plate, u.fullname 
    FROM bookings b
    JOIN vehicles v ON b.vehicle_id = v.id
    JOIN users u ON b.user_id = u.id
    WHERE b.status = ?
";

// DESPUÉS - Usar getRawConnection() y añadir filtros manuales
$db = getTenantDB()->getRawConnection();
$tenantId = CURRENT_TENANT_ID;

$query = "
    SELECT b.*, v.plate, u.fullname 
    FROM bookings b
    JOIN vehicles v ON b.vehicle_id = v.id
    JOIN users u ON b.user_id = u.id
    WHERE b.status = ?
      AND b.tenant_id = ?
      AND v.tenant_id = ?
      AND u.tenant_id = ?
";

$stmt = $db->prepare($query);
$stmt->bind_param("siii", $status, $tenantId, $tenantId, $tenantId);
```

#### Uploads de Archivos

```php
// Separar archivos por tenant
$uploadDir = __DIR__ . '/uploads/' . CURRENT_TENANT_ID . '/';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = $uploadDir . basename($_FILES['file']['name']);
move_uploaded_file($_FILES['file']['tmp_name'], $filename);
```

#### Cron Jobs / Background Tasks

```php
<?php
// Para procesar todos los tenants
require_once __DIR__ . '/config/database.php';

$tenantManager = getTenantManager();
$tenants = $tenantManager->getAllTenants('active');

foreach ($tenants as $tenant) {
    echo "Processing tenant: {$tenant['name']}\n";
    
    // Cambiar contexto al tenant
    $tenantManager->switchTenant($tenant['id']);
    
    // Ejecutar tarea para este tenant
    $db = getTenantDB();
    $pendingBookings = $db->select('bookings', '*', 'status = ?', ['pending']);
    
    // ... procesar bookings
}
?>
```

### Paso 5: Testing

#### Test de Aislamiento de Datos

```php
<?php
// test-tenant-isolation.php
require_once 'config/database.php';

$tenantManager = getTenantManager();

// Crear datos en tenant 1
$tenantManager->switchTenant(1);
$db1 = getTenantDB();
$user1Id = $db1->insert('users', [
    'email' => 'test1@tenant1.com',
    'username' => 'tenant1user',
    'password' => password_hash('test', PASSWORD_DEFAULT)
]);
echo "Created user $user1Id in tenant 1\n";

// Cambiar a tenant 2
$tenantManager->switchTenant(2);
$db2 = getTenantDB();

// Intentar buscar usuario de tenant 1 (no debe encontrarlo)
$user = $db2->selectOne('users', '*', 'email = ?', ['test1@tenant1.com']);

if ($user === null) {
    echo "✅ PASS: Tenant isolation works correctly\n";
} else {
    echo "❌ FAIL: Data leaked between tenants!\n";
}
?>
```

### Paso 6: Verificación Final

```bash
# 1. Verificar que todos los archivos tienen TenantBootstrap
grep -r "require.*TenantBootstrap" public_html/php/ --include="*.php"

# 2. Buscar queries directas que podrían saltarse el filtro
grep -r "SELECT.*FROM.*WHERE" public_html/ --include="*.php" | grep -v "tenant_id"

# 3. Verificar que no hay tenant_id en POST/GET
grep -r "\$_POST\['tenant_id'\]" public_html/ --include="*.php"
grep -r "\$_GET\['tenant_id'\]" public_html/ --include="*.php"
```

### Errores Comunes

#### ❌ Error 1: Olvidar TenantBootstrap
```php
// MALO - No identifica tenant
$db = getTenantDB();
$users = $db->select('users');
```

```php
// BUENO
require_once 'php/core/TenantBootstrap.php';
$db = getTenantDB();
$users = $db->select('users');
```

#### ❌ Error 2: Usar getDB() en lugar de getTenantDB()
```php
// MALO - No filtra por tenant
$db = getDB();
$result = $db->query("SELECT * FROM vehicles");
```

```php
// BUENO
$db = getTenantDB();
$result = $db->select('vehicles');
```

#### ❌ Error 3: Confiar en tenant_id del cliente
```php
// MALO - Vulnerabilidad de seguridad
$tenantId = $_POST['tenant_id'];
$query = "SELECT * FROM users WHERE tenant_id = $tenantId";
```

```php
// BUENO
$tenantId = CURRENT_TENANT_ID;
$db = getTenantDB(); // Ya filtra automáticamente
```

## Resumen de Cambios Necesarios

### En cada archivo PHP:

1. ✅ Añadir `require_once 'php/core/TenantBootstrap.php'` al inicio
2. ✅ Reemplazar `getDB()` con `getTenantDB()`
3. ✅ Usar métodos de `TenantAwareDatabase`:
   - `select()` en lugar de `query(SELECT ...)`
   - `insert()` en lugar de `query(INSERT ...)`
   - `update()` en lugar de `query(UPDATE ...)`
   - `delete()` en lugar de `query(DELETE ...)`
4. ✅ Añadir verificación de tenant en sesiones con `verifyTenantSession()`
5. ✅ Usar constantes `CURRENT_TENANT_ID`, `CURRENT_TENANT_NAME`
6. ✅ Nunca confiar en `$_POST['tenant_id']` o `$_GET['tenant_id']`

### Herramientas Útiles

```bash
# Script para contar archivos migrados
find public_html -name "*.php" -exec grep -l "TenantBootstrap" {} \; | wc -l

# Script para encontrar archivos sin migrar
find public_html -name "*.php" -type f ! -exec grep -q "TenantBootstrap" {} \; -print
```

## Siguiente Paso

Una vez completada la migración, ejecutar:

```bash
mysql -u root -p simsdb < multitenant-schema.sql
```

Y acceder al super admin panel:
```
http://localhost:8080/superadmin-login.html
```

Credenciales:
- Username: `superadmin`
- Password: `superadmin123`
