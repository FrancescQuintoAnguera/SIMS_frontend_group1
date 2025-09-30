<?php
if (isset($_COOKIE['user_data'])) {
    list($username, $password) = explode('|', $_COOKIE['user_data']);
    $mensaje = "Bienvenido, $username. La cookie se ha guardado correctamente.";
    $isLoggedIn = true;
} else {
    $mensaje = "No se encontró ninguna cookie. Por favor, inicia sesión de nuevo.";
    $isLoggedIn = false;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EzyRide</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header class="header">
        <h1>EzyRide</h1>
        
        <!-- Menú hamburguesa -->
        <div class="hamburger-menu" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <!-- Menú desplegable -->
        <div class="menu-dropdown" id="menuDropdown">
            <a href="#" onclick="searchAddress()">Buscar Dirección</a>
            <a href="#" onclick="changeLanguage()">Idioma</a>
            <a href="#" onclick="contactSupport()">Soporte al Cliente</a>
            <?php if ($isLoggedIn): ?>
                <a href="#" onclick="showProfile('<?php echo $username; ?>')">Perfil</a>
                <a href="logout.php">Cerrar Sesión</a>
            <?php else: ?>
                <a href="login.php">Iniciar Sesión</a>
            <?php endif; ?>
        </div>

        <!-- Navegación para pantallas grandes -->
        <div class="nav-right">
            <?php if (!$isLoggedIn): ?>
                <a href="login.php"><button class="header-button">Conduce ya</button></a>
            <?php endif; ?>
            <a class="offers" href="#">Ofertas</a>
        </div>
    </header>

    <main>
        <br>
        <br>
        <h1>Verificación</h1>
        <p><?php echo $mensaje; ?></p>
        <a href="index.html"><button>Volver</button></a>
    </main>

    <!-- Incluir el archivo JavaScript -->
    <script src="script.js"></script>
</body>
</html>