<?php
// Leer sesiones desde archivo JSON (sin usar $_SESSION)
define('SESSIONS_FILE', __DIR__ . '/../../../data/sessions.json');

function getSessionsFromFile() {
    if (!file_exists(SESSIONS_FILE)) {
        return [];
    }
    $content = file_get_contents(SESSIONS_FILE);
    return json_decode($content, true) ?? [];
}

$hasAuthCookie = isset($_COOKIE['authToken']);
$username = null;

if ($hasAuthCookie) {
    $token = $_COOKIE['authToken'];
    $sessions = getSessionsFromFile();
    
    if (isset($sessions[$token])) {
        $username = $sessions[$token]['username'];
    }
}
?>

<header>
    <button id="menu-toggle" class="menu-button" aria-label="Abrir menú">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <img src="/common/images/logoName.png" alt="logo">

    <?php if ($username): ?>
        <span class="username-display">
            Hola, <?php echo htmlspecialchars($username); ?>
        </span>
    <?php else: ?>
        <button id="register-button">
            Regístrate
        </button>
    <?php endif; ?>

</header>
