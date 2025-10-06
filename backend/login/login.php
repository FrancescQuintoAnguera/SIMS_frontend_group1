<?php
// backend/login/login.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Iniciar sesión
session_start();

// Incluir archivo de configuración de base de datos si lo tienes
// require_once '../config/database.php';

// Obtener datos del POST
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $data['password'] ?? '';
    
    // Validaciones básicas
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email y contraseña son requeridos'
        ]);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email inválido'
        ]);
        exit;
    }
    
    // AQUÍ CONECTARÍAS CON TU BASE DE DATOS
    // Ejemplo con PDO:
    /*
    try {
        $stmt = $pdo->prepare("SELECT id, email, password, nombre FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_nombre'] = $user['nombre'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso',
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'nombre' => $user['nombre']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error en el servidor'
        ]);
    }
    */
    
    // MIENTRAS TANTO, SIMULACIÓN PARA TESTING:
    if ($email === 'test@ezyride.com' && $password === '123456') {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_nombre'] = 'Usuario Test';
        
        echo json_encode([
            'success' => true,
            'message' => 'Login exitoso',
            'user' => [
                'id' => 1,
                'email' => $email,
                'nombre' => 'Usuario Test'
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Credenciales incorrectas'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>