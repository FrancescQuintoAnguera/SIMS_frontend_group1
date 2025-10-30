# 🚗 EazyRide Multi-Tenant - Sistema de Carsharing

## 🎯 ¿Qué es esto?

EazyRide ha sido **refactorizado completamente** de un sistema single-tenant a una **arquitectura multi-tenant**, permitiendo que múltiples empresas de carsharing operen de forma independiente en la misma plataforma.

### ✨ Características Principales

🏢 **Multi-Tenant**: Múltiples empresas en una sola instalación  
🔒 **Aislamiento Total**: Cada empresa solo ve sus datos  
👑 **Super Admin Panel**: Gestión centralizada de todos los tenants  
🚀 **Escalable**: Añade nuevos tenants sin reconfigurar  
💰 **Facturación**: Sistema de suscripciones y comisiones  
📊 **Analytics**: Métricas por tenant y globales  
🎨 **Customizable**: Cada tenant puede tener su imagen de marca  

---

## 📁 Estructura del Proyecto

```
EazyRide_edit/
├── multitenant-schema.sql          # Schema completo multi-tenant
├── MULTITENANT_ARCHITECTURE.md     # Documentación técnica detallada
├── MIGRATION_GUIDE.md              # Guía paso a paso de migración
├── 
├── public_html/
│   ├── superadmin.html             # Panel de super admin
│   ├── superadmin-login.html       # Login de super admin
│   │
│   ├── api/
│   │   └── superadmin/             # APIs del super admin
│   │       ├── dashboard.php
│   │       ├── tenants.php
│   │       ├── admins.php
│   │       ├── create-tenant.php
│   │       ├── login.php
│   │       └── logout.php
│   │
│   └── php/
│       └── core/
│           ├── TenantManager.php        # Gestión de tenants
│           ├── TenantAwareDatabase.php  # BD con filtro automático
│           ├── TenantBootstrap.php      # Inicialización de contexto
│           ├── DatabaseMariaDB.php
│           └── DatabaseMongo.php
│
└── config/
    └── database.php                # Conexión BD multi-tenant
```

---

## 🚀 Instalación Rápida

### 1. Aplicar el Schema Multi-Tenant

```bash
# Desde el directorio del proyecto
mysql -u root -p simsdb < multitenant-schema.sql
```

Esto creará:
- ✅ Tabla `tenants` (empresas)
- ✅ Tabla `tenant_admins` (administradores)
- ✅ Tabla `tenant_admin_access` (permisos)
- ✅ Columna `tenant_id` en todas las tablas
- ✅ Super admin por defecto
- ✅ Tenant demo con datos de ejemplo

### 2. Acceder al Super Admin Panel

```
URL: http://localhost:8080/superadmin-login.html

Credenciales:
  Username: superadmin
  Password: superadmin123
```

⚠️ **IMPORTANTE**: Cambiar la contraseña inmediatamente en producción.

### 3. Crear tu Primer Tenant

1. Iniciar sesión como super admin
2. Ir a pestaña **"Tenants"**
3. Click en **"Create New Tenant"**
4. Rellenar el formulario:
   - **Nombre**: Mi Empresa de Carsharing
   - **Subdomain**: miempresa
   - **Email**: contacto@miempresa.com
   - **Plan**: Professional
5. Click **"Create Tenant"**

Se creará automáticamente:
- ✅ El tenant en la base de datos
- ✅ Un admin por defecto para ese tenant
- ✅ Configuración inicial

---

## 🏗️ Arquitectura Multi-Tenant

### Comparación: Before vs After

#### ❌ ANTES (Single-Tenant)

```
┌─────────────────────────┐
│    UNA SOLA EMPRESA     │
│                         │
│  Users   Vehicles       │
│  Bookings  Payments     │
└─────────────────────────┘
```

#### ✅ AHORA (Multi-Tenant)

```
┌──────────────────────────────────────┐
│        SUPER ADMIN PANEL             │
│   (Gestiona múltiples empresas)     │
└────────────┬─────────────────────────┘
             │
    ┌────────┴───────┬─────────────┐
    │                │             │
┌───▼──────┐  ┌─────▼────┐  ┌────▼─────┐
│ TENANT 1 │  │ TENANT 2 │  │ TENANT 3 │
│ Empresa A│  │ Empresa B│  │ Empresa C│
│          │  │          │  │          │
│ - Users  │  │ - Users  │  │ - Users  │
│ -Vehicles│  │ -Vehicles│  │ -Vehicles│
│ -Bookings│  │ -Bookings│  │ -Bookings│
└──────────┘  └──────────┘  └──────────┘
```

### Flujo de Datos

```
1. Usuario accede: demo.eazyride.com
                    ↓
2. TenantBootstrap identifica tenant por subdomain
                    ↓
3. Se establece contexto: CURRENT_TENANT_ID = 1
                    ↓
4. Todas las consultas filtran por tenant_id = 1
                    ↓
5. Usuario solo ve datos de su empresa
```

---

## 💻 Uso para Desarrolladores

### Refactorizar Código Existente

#### Archivo Típico (ANTES)

```php
<?php
require_once '../config/database.php';
$db = getDB();

$stmt = $db->prepare("SELECT * FROM vehicles WHERE status = ?");
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();
?>
```

#### Archivo Refactorizado (DESPUÉS)

```php
<?php
// 1. Incluir TenantBootstrap al inicio
require_once __DIR__ . '/../php/core/TenantBootstrap.php';

// 2. Usar getTenantDB() que filtra automáticamente
$db = getTenantDB();

// 3. Usar métodos tenant-aware
$result = $db->select('vehicles', '*', 'status = ?', [$status]);

// Los datos ya están filtrados por tenant_id automáticamente
?>
```

### Funciones Principales

```php
// Obtener tenant actual
$tenant = getCurrentTenant();
echo $tenant['name']; // "Demo CarSharing Company"

// Usar constantes
echo CURRENT_TENANT_ID;        // 1
echo CURRENT_TENANT_NAME;      // "Demo CarSharing Company"
echo CURRENT_TENANT_SUBDOMAIN; // "demo"

// Base de datos tenant-aware
$db = getTenantDB();

// Todas las operaciones se filtran automáticamente
$users = $db->select('users');
$vehicles = $db->select('vehicles', '*', 'status = ?', ['available']);
$userId = $db->insert('users', ['email' => 'new@user.com', ...]);

// Verificar acceso del usuario
if (verifyTenantSession()) {
    // Usuario autenticado y pertenece al tenant actual
}
```

---

## 📚 Documentación Completa

### 📖 Archivos de Documentación

1. **[MULTITENANT_ARCHITECTURE.md](./MULTITENANT_ARCHITECTURE.md)**
   - Arquitectura detallada
   - Clases y componentes
   - API Reference completa
   - Ejemplos de código
   - Seguridad y best practices

2. **[MIGRATION_GUIDE.md](./MIGRATION_GUIDE.md)**
   - Guía paso a paso de migración
   - Patrones de refactorización
   - Casos especiales (JOINs, uploads, etc.)
   - Scripts de verificación
   - Testing de aislamiento

---

## 🎯 Casos de Uso

### Para Empresas SaaS

Ofrecer tu plataforma de carsharing a múltiples clientes:
- Cada cliente tiene su propio subdomain: `cliente1.tuapp.com`
- Datos completamente aislados
- Facturación automática por uso
- Panel de gestión para cada cliente

### Para Franquicias

Gestionar múltiples sucursales de tu empresa:
- `barcelona.eazyride.com`
- `madrid.eazyride.com`
- `valencia.eazyride.com`

Cada sucursal gestiona su flota independientemente.

### Para Testing

Crear tenants de prueba sin afectar producción:
- `test.eazyride.com`
- `staging.eazyride.com`
- `demo.eazyride.com`

---

## 🔐 Seguridad

### Aislamiento de Datos

✅ **Automático**: `TenantAwareDatabase` filtra por tenant_id  
✅ **Verificación de Sesión**: `verifyTenantSession()`  
✅ **Sin Input del Usuario**: tenant_id NUNCA viene de POST/GET  
✅ **Foreign Keys**: Cascada en eliminación de tenant  

### Best Practices

```php
// ❌ NUNCA hacer esto
$tenantId = $_POST['tenant_id']; // INSEGURO

// ✅ SIEMPRE hacer esto
$tenantId = CURRENT_TENANT_ID; // SEGURO
```

---

## 🧪 Testing

### Test de Aislamiento

```bash
# Crear archivo test-isolation.php
php test-isolation.php

# Debe mostrar:
# ✅ PASS: Tenant isolation works correctly
```

### Verificación de Migración

```bash
# Ver qué archivos faltan por migrar
find public_html -name "*.php" -type f ! -exec grep -q "TenantBootstrap" {} \; -print

# Contar archivos migrados
find public_html -name "*.php" -exec grep -l "TenantBootstrap" {} \; | wc -l
```

---

## 📊 Base de Datos

### Nuevas Tablas

| Tabla | Propósito |
|-------|-----------|
| `tenants` | Información de cada empresa |
| `tenant_admins` | Super admins y admins de tenant |
| `tenant_admin_access` | Permisos de acceso |
| `tenant_analytics` | Métricas por tenant |
| `tenant_billing` | Facturación |

### Tablas Modificadas

Todas las tablas principales ahora tienen:
- `tenant_id INT` - ID del tenant propietario
- `FOREIGN KEY (tenant_id) REFERENCES tenants(id)`
- `INDEX idx_tenant_id (tenant_id)`

---

## 🛠️ Comandos Útiles

```bash
# Aplicar schema
mysql -u root -p simsdb < multitenant-schema.sql

# Ver tenants creados
mysql -u root -p -e "SELECT * FROM simsdb.tenants"

# Ver super admins
mysql -u root -p -e "SELECT * FROM simsdb.tenant_admins"

# Backup por tenant
mysqldump -u root -p simsdb \
  --where="tenant_id=1" \
  users vehicles bookings > tenant1_backup.sql
```

---

## 🔄 Migración de Código Existente

### Proceso Rápido

1. **Añadir TenantBootstrap** al inicio de cada archivo PHP
2. **Reemplazar** `getDB()` → `getTenantDB()`
3. **Usar métodos** de `TenantAwareDatabase`
4. **Verificar** con `verifyTenantSession()`
5. **Probar** con múltiples tenants

### Script de Ayuda

```bash
# Ver guía completa
cat MIGRATION_GUIDE.md

# Buscar archivos que necesitan migración
grep -L "TenantBootstrap" public_html/**/*.php
```

---

## 🎨 Personalización por Tenant

Cada tenant puede tener:

```php
$tenant = getCurrentTenant();

// Colores personalizados
$primaryColor = $tenant['primary_color'];    // #007bff
$secondaryColor = $tenant['secondary_color']; // #6c757d

// Logo
$logo = $tenant['logo_url']; // /uploads/tenants/1/logo.png

// Configuración
$settings = json_decode($tenant['settings'], true);
$timezone = $tenant['timezone'];   // Europe/Madrid
$currency = $tenant['currency'];   // EUR
$language = $tenant['language'];   // es
```

---

## 📞 Soporte

### Documentación
- 📄 [Arquitectura Completa](./MULTITENANT_ARCHITECTURE.md)
- 📋 [Guía de Migración](./MIGRATION_GUIDE.md)

### Contacto
- 📧 Email: support@eazyride.com
- 💬 Issues: GitHub Issues

---

## 🚀 Roadmap

### ✅ Fase 1: Completado (v2.0)
- [x] Schema multi-tenant
- [x] TenantManager
- [x] TenantAwareDatabase
- [x] Super Admin Panel
- [x] Aislamiento de datos
- [x] Documentación completa

### 🔄 Fase 2: En Progreso
- [ ] Sistema de facturación
- [ ] Analytics avanzados por tenant
- [ ] Temas personalizables
- [ ] API pública para tenants
- [ ] MongoDB multi-tenant

### 🔮 Fase 3: Futuro
- [ ] White-label completo
- [ ] Marketplace de integraciones
- [ ] Mobile apps por tenant
- [ ] Backup/Restore automatizado
- [ ] Multi-región (geografía)

---

## 📝 Changelog

### Version 2.0.0 (Octubre 2025) - Multi-Tenant
- 🎉 **Nueva arquitectura multi-tenant completa**
- ✨ Super Admin Panel
- 🔒 Aislamiento total de datos
- 📊 Gestión de múltiples empresas
- 🛠️ Herramientas de migración
- 📚 Documentación completa

### Version 1.0.0 - Single-Tenant
- Sistema básico de carsharing
- Una sola empresa
- Admin panel simple

---

## 📄 Licencia

[Tu Licencia Aquí]

---

## 🙏 Contribuir

Para contribuir al proyecto:
1. Fork el repositorio
2. Crear branch: `git checkout -b feature/nueva-funcionalidad`
3. Commit: `git commit -am 'Add nueva funcionalidad'`
4. Push: `git push origin feature/nueva-funcionalidad`
5. Crear Pull Request

---

## ⚡ Quick Start

```bash
# 1. Aplicar schema
mysql -u root -p simsdb < multitenant-schema.sql

# 2. Acceder al super admin
open http://localhost:8080/superadmin-login.html

# 3. Login
Username: superadmin
Password: superadmin123

# 4. Crear tu primer tenant
Click en "Create New Tenant" → Rellenar formulario → Create

# 5. ¡Listo! Ahora tienes un sistema multi-tenant funcionando 🎉
```

---

**Versión:** 2.0.0 Multi-Tenant  
**Última actualización:** Octubre 2025  
**Autor:** EazyRide Development Team
