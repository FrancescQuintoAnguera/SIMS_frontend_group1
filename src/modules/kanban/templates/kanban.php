<?php
$estado = isset($_COOKIE['tablero_estado']) ? $_COOKIE['tablero_estado'] : '[]';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Kanban</title>
  <link rel="stylesheet" href="/modules/kanban/styles/kanban.css">
</head>
<body>

<h1>Tablero Kanban</h1>
<button id="nuevaColumna">Nueva Columna</button>
<button id="nuevaTarea">Nueva Tarea</button>

<div class="tablero"></div>
<script>
const estadoGuardado = <?php echo $estado; ?>;
</script>

<script src="/modules/kanban/script/kanban.js"></script>

</body>
</html>
