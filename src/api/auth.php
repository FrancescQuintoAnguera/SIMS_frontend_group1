<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');

// NO usar session_start() - usamos solo cookies y archivos JSON

define('COOKIE_NAME', 'authToken');
define('COOKIE_EXPIRY', 12 * 60 * 60); 
define('SESSIONS_FILE', __DIR__ . '/../data/sessions.json');

function getUsers() {
    $usersFile = __DIR__ . '/../data/users.json';
    
    if (!file_exists($usersFile)) {
        $defaultUsers = [
            ['id' => 1, 'username' => 'client', 'email' => 'client@test.dev', 'password' => password_hash('1234', PASSWORD_DEFAULT), 'role' => 2],
            ['id' => 2, 'username' => 'worker', 'email' => 'worker@test.dev', 'password' => password_hash('1234', PASSWORD_DEFAULT), 'role' => 3],
            ['id' => 3, 'username' => 'car-admin', 'email' => 'caradmin@test.dev', 'password' => password_hash('1234', PASSWORD_DEFAULT), 'role' => 3],
            ['id' => 4, 'username' => 'super-admin', 'email' => 'super@test.dev', 'password' => password_hash('1234', PASSWORD_DEFAULT), 'role' => 4],
            ['id' => 5, 'username' => 'guest', 'email' => '', 'password' => '', 'role' => 1]
        ];
        
        if (!is_dir(__DIR__ . '/../data')) {
            mkdir(__DIR__ . '/../data', 0777, true);
        }
        
        file_put_contents($usersFile, json_encode($defaultUsers, JSON_PRETTY_PRINT));
        return $defaultUsers;
    }
    
    return json_decode(file_get_contents($usersFile), true);
}

function saveUsers($users) {
    $usersFile = __DIR__ . '/../data/users.json';
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
}

// Función para leer sesiones del archivo
function getSessions() {
    if (!file_exists(SESSIONS_FILE)) {
        return [];
    }
    
    $content = file_get_contents(SESSIONS_FILE);
    return json_decode($content, true) ?? [];
}

// Función para guardar sesiones en archivo
function saveSessions($sessions) {
    if (!is_dir(dirname(SESSIONS_FILE))) {
        mkdir(dirname(SESSIONS_FILE), 0777, true);
    }
    file_put_contents(SESSIONS_FILE, json_encode($sessions, JSON_PRETTY_PRINT));
}

// Limpiar sesiones expiradas
function cleanExpiredSessions() {
    $sessions = getSessions();
    $now = time();
    $cleaned = [];
    
    foreach ($sessions as $token => $data) {
        // Mantener solo sesiones no expiradas (12 horas para usuarios, 24 para guest)
        $expiry = $data['role'] === 1 ? (24 * 60 * 60) : COOKIE_EXPIRY;
        if (($data['createdAt'] + $expiry) > $now) {
            $cleaned[$token] = $data;
        }
    }
    
    if (count($cleaned) !== count($sessions)) {
        saveSessions($cleaned);
    }
    
    return $cleaned;
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

// Función para guardar sesión en archivo
function saveSession($token, $userData) {
    $sessions = cleanExpiredSessions();
    
    $sessions[$token] = [
        'userId' => $userData['id'],
        'username' => $userData['username'],
        'email' => $userData['email'],
        'role' => $userData['role'],
        'createdAt' => time()
    ];
    
    saveSessions($sessions);
}

// Función para obtener sesión actual desde archivo
function getCurrentSession() {
    if (!isset($_COOKIE[COOKIE_NAME])) {
        return null;
    }
    
    $token = $_COOKIE[COOKIE_NAME];
    $sessions = cleanExpiredSessions();
    
    if (!isset($sessions[$token])) {
        return null;
    }
    
    return $sessions[$token];
}

// Función para eliminar una sesión
function deleteSession($token) {
    $sessions = getSessions();
    
    if (isset($sessions[$token])) {
        unset($sessions[$token]);
        saveSessions($sessions);
    }
}

// Obtener la acción
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        $data = json_decode(file_get_contents('php://input'), true);
        $identifier = $data['identifier'] ?? '';
        $password = $data['password'] ?? '';
        
        $users = getUsers();
        $user = null;
        
        foreach ($users as $u) {
            if (($u['email'] === $identifier || $u['username'] === $identifier)) {
                if ($u['role'] === 1 || password_verify($password, $u['password'])) {
                    $user = $u;
                    break;
                }
            }
        }
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
            exit;
        }
        
        $token = generateToken();
        saveSession($token, $user);
        
        // Crear cookie con 12 horas de expiración
        setcookie(COOKIE_NAME, $token, [
            'expires' => time() + COOKIE_EXPIRY,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        echo json_encode([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
        break;
        
    case 'register':
        $data = json_decode(file_get_contents('php://input'), true);
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        
        $errors = [];
        
        // Validaciones
        if (empty($username)) $errors['username'] = 'Campo vacío';
        if (empty($email)) $errors['email'] = 'Campo vacío';
        if (empty($password)) $errors['password'] = 'Campo vacío';
        
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }
        
        // Validar formato
        if (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username)) {
            $errors['username'] = 'El username debe tener 3-20 caracteres (letras, números, _ -)';
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El email no es válido';
        }
        
        if (strlen($password) < 4) {
            $errors['password'] = 'La contraseña debe tener al menos 4 caracteres';
        }
        
        $users = getUsers();
        
        // Verificar usuario único
        foreach ($users as $u) {
            if ($u['username'] === $username) {
                $errors['username'] = 'El nombre de usuario ya existe';
            }
            if ($u['email'] === $email) {
                $errors['email'] = 'El email ya está registrado';
            }
        }
        
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }
        
        // Crear nuevo usuario
        $newUser = [
            'id' => max(array_column($users, 'id')) + 1,
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 2
        ];
        
        $users[] = $newUser;
        saveUsers($users);
        
        // Auto-login
        $token = generateToken();
        saveSession($token, $newUser);
        
        setcookie(COOKIE_NAME, $token, [
            'expires' => time() + COOKIE_EXPIRY,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        echo json_encode([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $newUser['id'],
                'username' => $newUser['username'],
                'email' => $newUser['email'],
                'role' => $newUser['role']
            ]
        ]);
        break;
        
    case 'logout':
        $token = $_COOKIE[COOKIE_NAME] ?? null;
        
        if ($token) {
            deleteSession($token);
        }
        
        setcookie(COOKIE_NAME, '', [
            'expires' => time() - 3600,
            'path' => '/'
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Logout exitoso']);
        break;
        
    case 'getCurrentUser':
        $session = getCurrentSession();
        
        if (!$session) {
            echo json_encode(['success' => false, 'user' => null]);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'user' => [
                'userId' => $session['userId'],
                'username' => $session['username'],
                'email' => $session['email'],
                'role' => $session['role']
            ]
        ]);
        break;
        
    case 'loginAsGuest':
        $users = getUsers();
        $guestUser = null;
        
        foreach ($users as $u) {
            if ($u['role'] === 1) {
                $guestUser = $u;
                break;
            }
        }
        
        if (!$guestUser) {
            echo json_encode(['success' => false, 'message' => 'Usuario invitado no encontrado']);
            exit;
        }
        
        $token = generateToken();
        saveSession($token, $guestUser);
        
        // Cookie de 24 horas para invitado
        setcookie(COOKIE_NAME, $token, [
            'expires' => time() + (24 * 60 * 60),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        echo json_encode(['success' => true, 'token' => $token]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}
