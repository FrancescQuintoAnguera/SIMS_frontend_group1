<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $telefono = $_POST['telefono'];

    // Archivos subidos
    $dni = $_FILES['dni'];
    $carnet = $_FILES['carnet'];
    $fotoData = $_POST['foto'];

    $carpeta = "uploads/";
    if (!is_dir($carpeta)) mkdir($carpeta);

    // Guardar carnet y DNI
    $dniPath = $carpeta . basename($dni['name']);
    $carnetPath = $carpeta . basename($carnet['name']);
    move_uploaded_file($dni['tmp_name'], $dniPath);
    move_uploaded_file($carnet['tmp_name'], $carnetPath);

    // Guardar foto tomada con la cámara
    if (strpos($fotoData, 'data:image/png;base64,') === 0) {
        $fotoData = base64_decode(substr($fotoData, strlen('data:image/png;base64,')));
        $fotoPath = $carpeta . uniqid('foto_') . '.png';
        file_put_contents($fotoPath, $fotoData);
    } else {
        $fotoPath = '';
    }

    // Guardar datos en CSV
    $datos = "$email,$telefono,$dniPath,$carnetPath,$fotoPath,$pass\n";
    file_put_contents("usuarios.csv", $datos, FILE_APPEND);

    exit;
}
?>
