<?php
// src/db.php
// Centralized PDO connection for PostgreSQL

// Database connection parameters
$host = 'host.docker.internal';   // o 'localhost' si no usas Docker
$port = '5432';
$dbname = 'userdatabase';
$user = 'joel';
$password = '123456';

try {
    // Data Source Name (DSN)
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    
    // Create PDO instance
    $pdo = new PDO($dsn, $user, $password);
    
    // Set error mode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Connection failed
    echo "<div style='padding:20px; border:2px solid red; background-color:#fdd; font-family:monospace;'>";
    echo "<h1>🔴 DATABASE CONNECTION ERROR</h1>";
    echo "<p>Please check your credentials in 'src/db.php'</p>";
    echo "<hr>";
    echo "<strong>PDO message:</strong> " . $e->getMessage();
    echo "</div>";
    die();
}
