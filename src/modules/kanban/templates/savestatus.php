<?php
$estado = file_get_contents('php://input');
setcookie('tablero_estado', $estado, time() + (7 * 24 * 60 * 60), '/');
echo json_encode(['success' => true]);
?>