<?php
// Script de debug para ver qué está fallando

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

echo "=== DEBUG EAZYPOINTS ===\n\n";

// 1. Verificar sesión
session_start();
echo "1. Sesión:\n";
echo "   User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NO EXISTE') . "\n";
echo "   Usuario: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'NO EXISTE') . "\n\n";

// 2. Verificar DatabaseMariaDB
echo "2. Archivo DatabaseMariaDB:\n";
$dbFile = __DIR__ . '/../core/DatabaseMariaDB.php';
echo "   Ruta: $dbFile\n";
echo "   Existe: " . (file_exists($dbFile) ? 'SÍ' : 'NO') . "\n\n";

if (!file_exists($dbFile)) {
    echo "ERROR: No se encuentra DatabaseMariaDB.php\n";
    exit;
}

require_once $dbFile;

// 3. Conectar a la base de datos
echo "3. Conexión a base de datos:\n";
try {
    $db = DatabaseMariaDB::getConnection();
    echo "   Estado: CONECTADO\n";
    echo "   Tipo: " . get_class($db) . "\n\n";
} catch (Exception $e) {
    echo "   Estado: ERROR\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
    exit;
}

// 4. Verificar tablas
echo "4. Verificando tablas:\n";
$tables_to_check = ['users', 'user_points', 'point_transactions', 'premium_subscriptions'];

foreach ($tables_to_check as $table) {
    $result = $db->query("SHOW TABLES LIKE '$table'");
    $exists = $result && $result->rowCount() > 0;
    echo "   $table: " . ($exists ? 'EXISTE ✓' : 'NO EXISTE ✗') . "\n";
}
echo "\n";

// 5. Verificar estructura de user_points
echo "5. Estructura de user_points:\n";
$result = $db->query("SHOW TABLES LIKE 'user_points'");
if ($result && $result->rowCount() > 0) {
    $columns = $db->query("DESCRIBE user_points");
    while ($col = $columns->fetch(PDO::FETCH_ASSOC)) {
        echo "   - {$col['Field']} ({$col['Type']})\n";
    }
} else {
    echo "   TABLA NO EXISTE\n";
}
echo "\n";

// 6. Verificar si el usuario tiene registro en user_points
if (isset($_SESSION['user_id'])) {
    echo "6. Registro de puntos del usuario:\n";
    $user_id = $_SESSION['user_id'];
    $stmt = $db->prepare("SELECT * FROM user_points WHERE user_id = ?");
    if ($stmt) {
        $stmt->execute([$user_id]);
        
        if ($result->rowCount() > 0) {
            $row = $result->fetch(PDO::FETCH_ASSOC);
            echo "   EXISTE ✓\n";
            echo "   Points: " . $row['points'] . "\n";
            echo "   Total purchased: " . $row['total_purchased'] . "\n";
            echo "   Total spent: " . $row['total_spent'] . "\n";
        } else {
            echo "   NO EXISTE ✗\n";
        }
    } else {
        echo "   ERROR al preparar query: " . $db->error . "\n";
    }
}

echo "\n=== FIN DEBUG ===\n";
?>
