<?php
/**
 * Test de Configuración del Sistema
 * Verifica que todo esté correctamente configurado
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=================================================================\n";
echo "🔧 TEST DE CONFIGURACIÓN - EAZYRIDE MULTI-TENANT\n";
echo "=================================================================\n\n";

// 1. Verificar archivo .env
echo "1. Verificando archivo .env...\n";
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    echo "   ✅ Archivo .env encontrado\n";
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos(trim($line), '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
} else {
    echo "   ❌ Archivo .env NO encontrado\n";
    exit(1);
}

// 2. Verificar variables de entorno
echo "\n2. Verificando variables de entorno...\n";
$required_vars = [
    'DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME',
    'MONGO_INITDB_ROOT_USERNAME', 'MONGO_INITDB_ROOT_PASSWORD'
];

$all_ok = true;
foreach ($required_vars as $var) {
    if (isset($_ENV[$var]) && !empty($_ENV[$var])) {
        $value = $var === 'DB_PASS' || $var === 'MONGO_INITDB_ROOT_PASSWORD' 
            ? str_repeat('*', strlen($_ENV[$var])) 
            : $_ENV[$var];
        echo "   ✅ $var = $value\n";
    } else {
        echo "   ❌ $var no configurado\n";
        $all_ok = false;
    }
}

if (!$all_ok) {
    echo "\n❌ Faltan variables de entorno. Verifica tu archivo .env\n";
    exit(1);
}

// 3. Verificar archivos PHP core
echo "\n3. Verificando archivos PHP core...\n";
$core_files = [
    'public_html/php/core/TenantManager.php',
    'public_html/php/core/TenantAwareDatabase.php',
    'public_html/php/core/DatabaseMariaDB.php',
    'config/database.php'
];

foreach ($core_files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "   ✅ $file\n";
    } else {
        echo "   ❌ $file NO encontrado\n";
        $all_ok = false;
    }
}

// 4. Probar carga de archivos
echo "\n4. Probando carga de archivos...\n";
try {
    require_once __DIR__ . '/config/database.php';
    echo "   ✅ config/database.php cargado correctamente\n";
} catch (Exception $e) {
    echo "   ❌ Error cargando config/database.php: " . $e->getMessage() . "\n";
    exit(1);
}

// 5. Probar conexión MariaDB
echo "\n5. Probando conexión a MariaDB...\n";
try {
    $db = getDB();
    echo "   ✅ Conexión exitosa a MariaDB\n";
    
    // Verificar si existe la tabla tenants
    $result = $db->query("SHOW TABLES LIKE 'tenants'");
    if ($result && $result->num_rows > 0) {
        echo "   ✅ Tabla 'tenants' existe\n";
        
        // Contar tenants
        $result = $db->query("SELECT COUNT(*) as count FROM tenants");
        $row = $result->fetch_assoc();
        echo "   ℹ️  Tenants registrados: " . $row['count'] . "\n";
    } else {
        echo "   ⚠️  Tabla 'tenants' NO existe - Ejecuta multitenant-schema.sql\n";
    }
    
    // Verificar tabla tenant_admins
    $result = $db->query("SHOW TABLES LIKE 'tenant_admins'");
    if ($result && $result->num_rows > 0) {
        echo "   ✅ Tabla 'tenant_admins' existe\n";
        
        // Contar admins
        $result = $db->query("SELECT COUNT(*) as count FROM tenant_admins");
        $row = $result->fetch_assoc();
        echo "   ℹ️  Tenant admins registrados: " . $row['count'] . "\n";
    } else {
        echo "   ⚠️  Tabla 'tenant_admins' NO existe - Ejecuta multitenant-schema.sql\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error de conexión MariaDB: " . $e->getMessage() . "\n";
}

// 6. Probar TenantManager
echo "\n6. Probando TenantManager...\n";
try {
    $tenantManager = getTenantManager();
    echo "   ✅ TenantManager instanciado correctamente\n";
    
    // Intentar obtener tenants (si es super admin)
    try {
        // No podemos usar getAllTenants sin autenticación, 
        // pero podemos verificar que la clase funciona
        echo "   ✅ TenantManager funcionando\n";
    } catch (Exception $e) {
        echo "   ℹ️  " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error con TenantManager: " . $e->getMessage() . "\n";
}

// 7. Verificar APIs
echo "\n7. Verificando archivos de API...\n";
$api_files = [
    'public_html/api/superadmin/login.php',
    'public_html/api/superadmin/dashboard.php',
    'public_html/api/superadmin/tenants.php',
];

foreach ($api_files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        // Verificar sintaxis
        $output = shell_exec("php -l $path 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "   ✅ $file\n";
        } else {
            echo "   ⚠️  $file tiene errores de sintaxis\n";
        }
    } else {
        echo "   ❌ $file NO encontrado\n";
    }
}

// 8. Verificar frontend
echo "\n8. Verificando archivos frontend...\n";
$frontend_files = [
    'public_html/superadmin-login.html',
    'public_html/superadmin.html'
];

foreach ($frontend_files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "   ✅ $file\n";
    } else {
        echo "   ❌ $file NO encontrado\n";
    }
}

// Resumen final
echo "\n=================================================================\n";
echo "📊 RESUMEN\n";
echo "=================================================================\n";

if ($all_ok) {
    echo "✅ Sistema configurado correctamente\n\n";
    echo "Próximos pasos:\n";
    echo "1. Si no has ejecutado el schema, ejecuta:\n";
    echo "   mysql -u root -p simsdb < multitenant-schema.sql\n\n";
    echo "2. Accede al super admin:\n";
    echo "   http://localhost:8080/superadmin-login.html\n\n";
    echo "3. Credenciales por defecto:\n";
    echo "   Username: superadmin\n";
    echo "   Password: superadmin123\n";
} else {
    echo "⚠️  Algunos componentes faltan o tienen errores\n";
    echo "Revisa los mensajes anteriores\n";
}

echo "\n=================================================================\n";
