<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
    $password = isset($_POST['password']) ? htmlspecialchars($_POST['password']) : '';

    if (isset($_COOKIE['user_data'])) {
        list($storedUsername, $storedPassword) = explode('|', $_COOKIE['user_data']);
        if ($username === $storedUsername && $password === $storedPassword) {
            header("Location: home.php");
            exit();
        } else {
            $error = "Nombre de usuario o contraseña incorrectos.";
        }
    } else {
        $error = "No estás registrado. Por favor, regístrate primero.";
    }
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
    <header>
        <h1>EzyRide</h1>
        <a class="offers">Ofertas</a>
        <a href="register.php"><button class="header-button">Conduce ya</button></a>
    </header>
    <div class="login-container">
        <h1><img src="images/logoblau.png" alt="Descripción de la imagen" width="300"></h1>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="post" action="">
            <input type="text" id="username" name="username" placeholder="Nombre de usuario" required>
            <input type="password" id="password" name="password" placeholder="Contraseña" required>
            <button type="submit" name="login">Iniciar Sesión</button>
        </form>
        <a href="#" class="forgot">¿Olvidaste tu contraseña?</a>
        <a href="register.php" class="guest">Registrarse</a>
        <a href="home.php" class="guest">Entrar como invitado</a>
    </div>

</body>
</html>