# 🏢 EazyRide Multi-Tenant Architecture

## Tabla de Contenidos
- [Introducción](#introducción)
- [Arquitectura](#arquitectura)
- [Instalación](#instalación)
- [Uso](#uso)
- [API Reference](#api-reference)
- [Ejemplos](#ejemplos)

---

## Introducción

EazyRide ha sido refactorizado de una arquitectura **single-tenant** a **multi-tenant**, permitiendo que múltiples empresas de carsharing utilicen la misma plataforma con datos completamente aislados.

### ¿Qué es Multi-Tenant?

Un sistema multi-tenant permite que múltiples organizaciones (tenants) compartan la misma infraestructura mientras mantienen sus datos completamente separados y seguros.

### Beneficios

✅ **Escalabilidad**: Una sola instalación sirve a múltiples empresas  
✅ **Aislamiento de Datos**: Cada tenant solo ve sus propios datos  
✅ **Gestión Centralizada**: Super admin controla todos los tenants  
✅ **Costos Reducidos**: Infraestructura compartida  
✅ **Actualizaciones Unificadas**: Una actualización beneficia a todos  

---

## Arquitectura

### Componentes Principales

```
┌─────────────────────────────────────────────────────┐
│           SUPER ADMIN PANEL                         │
│  (Gestión de todos los tenants)                     │
└─────────────────────────────────────────────────────┘
                     │
        ┌────────────┴────────────┐
        │                         │
┌───────▼──────┐         ┌───────▼──────┐
│   TENANT 1   │         │   TENANT 2   │
│  (Empresa A) │   ...   │  (Empresa B) │
│              │         │              │
│ - Users      │         │ - Users      │
│ - Vehicles   │         │ - Vehicles   │
│ - Bookings   │         │ - Bookings   │
└──────────────┘         └──────────────┘
```

### Estructura de Base de Datos

#### Tablas de Control Multi-Tenant

1. **`tenants`** - Información de cada empresa/organización
   - id, name, subdomain, domain
   - contact_email, contact_phone
   - subscription_plan, status
   - settings (JSON)

2. **`tenant_admins`** - Administradores que gestionan tenants
   - id, email, username, password
   - is_super_admin (puede gestionar todos los tenants)

3. **`tenant_admin_access`** - Mapeo de admins a tenants
   - tenant_admin_id, tenant_id, role

#### Tablas con Tenant Awareness

Todas las tablas principales ahora tienen `tenant_id`:
- users
- vehicles
- locations
- bookings
- subscriptions
- vehicle_usage
- payments

### Clases Principales

#### 1. TenantManager
```php
// Identifica y gestiona el contexto del tenant actual
$tenantManager = TenantManager::getInstance();
$tenant = $tenantManager->getCurrentTenant();
$tenantId = $tenantManager->getCurrentTenantId();
```

#### 2. TenantAwareDatabase
```php
// Base de datos con filtrado automático por tenant_id
$db = getTenantDB();
$users = $db->select('users'); // Solo usuarios del tenant actual
```

#### 3. TenantBootstrap
```php
// Inicializa el contexto del tenant en cada petición
require_once 'php/core/TenantBootstrap.php';
```

---

## Instalación

### 1. Ejecutar el Schema Multi-Tenant

```bash
mysql -u root -p simsdb < multitenant-schema.sql
```

Este script:
- Crea las tablas de control (tenants, tenant_admins, etc.)
- Añade `tenant_id` a todas las tablas existentes
- Migra los datos existentes al tenant por defecto
- Crea el super admin por defecto

### 2. Credenciales por Defecto

**Super Admin:**
- URL: `http://localhost:8080/superadmin-login.html`
- Username: `superadmin`
- Email: `superadmin@eazyride.com`
- Password: `superadmin123` ⚠️ **¡Cambiar inmediatamente!**

**Tenant Demo:**
- Subdomain: `demo`
- Acceso: `http://demo.localhost:8080` (requiere configuración DNS local)

### 3. Configuración DNS Local (Opcional)

Para probar subdominios localmente:

**Linux/Mac** - Editar `/etc/hosts`:
```
127.0.0.1 demo.localhost
127.0.0.1 company1.localhost
127.0.0.1 company2.localhost
```

**Windows** - Editar `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 demo.localhost
127.0.0.1 company1.localhost
```

---

## Uso

### Para Super Administradores

#### 1. Acceder al Panel
```
http://localhost:8080/superadmin-login.html
```

#### 2. Crear Nuevo Tenant
1. Ir a pestaña "Tenants"
2. Click en "Create New Tenant"
3. Rellenar formulario:
   - Nombre de la empresa
   - Subdomain (ej: `empresa1`)
   - Email de contacto
   - Plan de suscripción

#### 3. Gestionar Tenants
- Ver estadísticas de cada tenant
- Activar/Suspender tenants
- Editar configuración
- Ver facturación

### Para Desarrolladores

#### Implementar Tenant Awareness en PHP

```php
<?php
// 1. Incluir bootstrap al inicio
require_once __DIR__ . '/php/core/TenantBootstrap.php';

// 2. Obtener tenant actual
$tenant = getCurrentTenant();
echo "Empresa: " . $tenant['name'];

// 3. Usar base de datos tenant-aware
$db = getTenantDB();

// 4. Todas las consultas se filtran automáticamente
$users = $db->select('users'); // Solo usuarios del tenant actual
$vehicles = $db->select('vehicles', '*', 'status = ?', ['available']);

// 5. Insertar con tenant_id automático
$db->insert('vehicles', [
    'plate' => '1234ABC',
    'brand' => 'Tesla',
    'model' => 'Model 3',
    'status' => 'available'
    // tenant_id se añade automáticamente
]);

// 6. Verificar acceso del usuario
if (verifyTenantSession()) {
    // Usuario pertenece al tenant actual
} else {
    // Usuario no autorizado
    die('Access denied');
}
?>
```

#### Modificar Endpoints Existentes

**Antes (Single-Tenant):**
```php
$stmt = $db->prepare("SELECT * FROM vehicles WHERE status = ?");
$stmt->bind_param("s", $status);
```

**Después (Multi-Tenant):**
```php
require_once 'php/core/TenantBootstrap.php';
$tdb = getTenantDB();
$result = $tdb->select('vehicles', '*', 'status = ?', [$status]);
// tenant_id se filtra automáticamente
```

---

## API Reference

### TenantManager

#### `identifyTenant($identifier = null)`
Identifica el tenant actual por subdomain, domain o ID.

```php
$tenant = $tenantManager->identifyTenant();
$tenant = $tenantManager->identifyTenant(5); // Por ID
```

#### `getCurrentTenant()`
Obtiene información del tenant actual.

```php
$tenant = $tenantManager->getCurrentTenant();
// Retorna: ['id' => 1, 'name' => 'Demo', 'subdomain' => 'demo', ...]
```

#### `getCurrentTenantId()`
Obtiene solo el ID del tenant actual.

```php
$tenantId = $tenantManager->getCurrentTenantId();
```

#### `switchTenant($tenantId)`
Cambia el contexto al tenant especificado (solo super admin).

```php
$tenantManager->switchTenant(2);
```

#### `createTenant($data)`
Crea un nuevo tenant (solo super admin).

```php
$tenantId = $tenantManager->createTenant([
    'name' => 'Nueva Empresa',
    'subdomain' => 'nueva',
    'contact_email' => 'contacto@nueva.com',
    'subscription_plan' => 'professional'
]);
```

#### `getTenantStats($tenantId)`
Obtiene estadísticas de un tenant.

```php
$stats = $tenantManager->getTenantStats($tenantId);
// ['total_users' => 50, 'total_vehicles' => 25, ...]
```

### TenantAwareDatabase

#### `select($table, $columns, $where, $params, $orderBy, $limit)`
```php
$users = $db->select('users', '*', 'is_admin = ?', [true]);
$vehicles = $db->select('vehicles', ['id', 'plate', 'status']);
```

#### `selectOne($table, $columns, $where, $params)`
```php
$user = $db->selectOne('users', '*', 'email = ?', ['user@example.com']);
```

#### `insert($table, $data)`
```php
$userId = $db->insert('users', [
    'email' => 'new@user.com',
    'username' => 'newuser',
    'password' => password_hash('pass123', PASSWORD_DEFAULT)
]);
```

#### `update($table, $data, $where, $params)`
```php
$db->update('vehicles', 
    ['status' => 'maintenance'], 
    'id = ?', 
    [5]
);
```

#### `delete($table, $where, $params)`
```php
$db->delete('bookings', 'id = ?', [10]);
```

#### `count($table, $where, $params)`
```php
$activeVehicles = $db->count('vehicles', 'status = ?', ['available']);
```

---

## Ejemplos

### Ejemplo 1: Login con Tenant Awareness

```php
<?php
require_once 'php/core/TenantBootstrap.php';

$email = $_POST['email'];
$password = $_POST['password'];

// Buscar usuario solo en el tenant actual
$db = getTenantDB();
$user = $db->selectOne('users', '*', 'email = ?', [$email]);

if ($user && password_verify($password, $user['password'])) {
    // Verificar que el usuario pertenece al tenant actual
    if ($user['tenant_id'] == CURRENT_TENANT_ID) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['tenant_id'] = $user['tenant_id'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
    }
}
?>
```

### Ejemplo 2: Listar Vehículos del Tenant

```php
<?php
require_once 'php/core/TenantBootstrap.php';

header('Content-Type: application/json');

$db = getTenantDB();

// Obtener solo vehículos del tenant actual
$result = $db->select('vehicles', 
    ['id', 'plate', 'brand', 'model', 'status', 'battery_level'],
    'status = ?',
    ['available'],
    'brand ASC'
);

$vehicles = [];
while ($row = $result->fetch_assoc()) {
    $vehicles[] = $row;
}

echo json_encode([
    'success' => true,
    'tenant' => CURRENT_TENANT_NAME,
    'vehicles' => $vehicles
]);
?>
```

### Ejemplo 3: Crear Booking con Validación de Tenant

```php
<?php
require_once 'php/core/TenantBootstrap.php';

if (!verifyTenantSession()) {
    die(json_encode(['error' => 'Unauthorized']));
}

$userId = $_SESSION['user_id'];
$vehicleId = $_POST['vehicle_id'];

$db = getTenantDB();

// Verificar que el vehículo pertenece al mismo tenant
$vehicle = $db->selectOne('vehicles', '*', 'id = ?', [$vehicleId]);

if (!$vehicle) {
    die(json_encode(['error' => 'Vehicle not found']));
}

// Crear booking (tenant_id se añade automáticamente)
$bookingId = $db->insert('bookings', [
    'user_id' => $userId,
    'vehicle_id' => $vehicleId,
    'start_datetime' => date('Y-m-d H:i:s'),
    'end_datetime' => date('Y-m-d H:i:s', strtotime('+2 hours')),
    'status' => 'confirmed',
    'total_cost' => 20.00
]);

echo json_encode([
    'success' => true,
    'booking_id' => $bookingId
]);
?>
```

### Ejemplo 4: Dashboard de Super Admin

```php
<?php
session_start();
require_once 'config/database.php';

// Verificar super admin
if (!isset($_SESSION['is_super_admin']) || !$_SESSION['is_super_admin']) {
    header('Location: superadmin-login.html');
    exit;
}

$tenantManager = getTenantManager();
$tenants = $tenantManager->getAllTenants('active');

foreach ($tenants as $tenant) {
    $stats = $tenantManager->getTenantStats($tenant['id']);
    
    echo "<div class='tenant-card'>";
    echo "<h3>{$tenant['name']}</h3>";
    echo "<p>Users: {$stats['total_users']}</p>";
    echo "<p>Vehicles: {$stats['total_vehicles']}</p>";
    echo "<p>Revenue: €{$stats['total_revenue']}</p>";
    echo "</div>";
}
?>
```

---

## Migración de Código Existente

### Checklist de Migración

- [ ] Añadir `require_once 'php/core/TenantBootstrap.php'` al inicio
- [ ] Reemplazar `getDB()` con `getTenantDB()` donde corresponda
- [ ] Usar métodos de `TenantAwareDatabase` en lugar de consultas directas
- [ ] Verificar tenant en sesiones de usuario
- [ ] Actualizar queries SQL para quitar filtros manuales de tenant_id
- [ ] Probar con múltiples tenants
- [ ] Validar aislamiento de datos

---

## Seguridad

### Buenas Prácticas

1. **Nunca confiar en tenant_id del cliente**
   ```php
   // ❌ MALO
   $tenantId = $_POST['tenant_id'];
   
   // ✅ BUENO
   $tenantId = CURRENT_TENANT_ID;
   ```

2. **Siempre usar TenantAwareDatabase**
   ```php
   // ❌ MALO - no filtra por tenant
   $result = $db->query("SELECT * FROM users");
   
   // ✅ BUENO - filtra automáticamente
   $result = getTenantDB()->select('users');
   ```

3. **Verificar sesión del tenant**
   ```php
   if (!verifyTenantSession()) {
       die('Unauthorized');
   }
   ```

4. **Cambiar contraseñas por defecto**
   - Super admin: `superadmin123`
   - Admins de tenant creados automáticamente

---

## Troubleshooting

### Problema: "Tenant Not Found"
**Solución:** 
- Verificar subdomain en la URL
- Comprobar que el tenant existe en la tabla `tenants`
- Revisar configuración DNS local

### Problema: Usuario no puede acceder tras migración
**Solución:**
```sql
-- Verificar tenant_id del usuario
SELECT id, email, tenant_id FROM users WHERE email = 'usuario@example.com';

-- Si es NULL, asignar al tenant correcto
UPDATE users SET tenant_id = 1 WHERE email = 'usuario@example.com';
```

### Problema: Datos mezclados entre tenants
**Solución:**
- Verificar que se usa `getTenantDB()` en lugar de `getDB()`
- Comprobar que TenantBootstrap se incluye al inicio
- Revisar queries SQL directas que no usan TenantAwareDatabase

---

## Roadmap

### Fase 1: ✅ Completado
- [x] Schema multi-tenant
- [x] TenantManager
- [x] TenantAwareDatabase
- [x] Super Admin Panel
- [x] Tenant Bootstrap

### Fase 2: En Progreso
- [ ] Facturación por tenant
- [ ] Analytics por tenant
- [ ] Temas personalizables por tenant
- [ ] MongoDB multi-tenant

### Fase 3: Futuro
- [ ] API de integración para tenants
- [ ] Backup/Restore por tenant
- [ ] Exportación de datos
- [ ] White-label completo

---

## Soporte

Para preguntas o problemas:
- 📧 Email: support@eazyride.com
- 📚 Documentación: /docs
- 🐛 Reportar bugs: GitHub Issues

---

**Última actualización:** Octubre 2025  
**Versión:** 2.0.0 Multi-Tenant
