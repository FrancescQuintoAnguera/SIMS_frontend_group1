<?php

if (isset($_COOKIE['user_data'])) {
    $error = "Ya estás registrado. Por favor, inicia sesión.";
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
        $password = isset($_POST['password']) ? htmlspecialchars($_POST['password']) : '';
        $password2 = isset($_POST['password2']) ? htmlspecialchars($_POST['password2']) : '';

        if (!empty($username)) {
            if ($password === $password2) {
                $cookieValue = $username . '|' . $password;
                setcookie("user_data", $cookieValue, time() + (7 * 24 * 60 * 60), "/");
                header("Location: home.php");
                exit();
            } else {
                $error = "Las contraseñas no coinciden.";
            }
        } else {
            $error = "Por favor, ingresa un nombre de usuario.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>EzyRide</h1>
        <a class="offers">Ofertas</a>
        <a href="login.php"><button class="header-button">Conduce ya</button></a>
    </header>
    <div class="login-container">
        <h1><img src="images/logoblau.png" alt="Descripción de la imagen" width="300"></h1>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <?php if (!isset($_COOKIE['user_data'])): ?>
            <form method="post" action="">
                <input type="text" id="username" name="username" placeholder="Nombre de usuario" required>
                <input type="password" id="password" name="password" placeholder="Contraseña" required>
                <input type="password" id="password2" name="password2" placeholder="Vuelve a introducir la contraseña" required>
                <button type="submit" name="register">Registrarse</button>
            </form>
        <?php endif; ?>
        <a href="login.php" class="guest">Volver a Iniciar Sesión</a>
        <a href="index.html" class="guest">Pagina principal</a>
    </div>
</body>
</html>