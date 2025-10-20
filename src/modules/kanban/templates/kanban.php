<?php
$estado = isset($_COOKIE['tablero_estado']) ? $_COOKIE['tablero_estado'] : '[]';
?>
<!DOCTYPE html>
<html lang="es">
<link rel="stylesheet" href="/modules/kanban/styles/kanban.css">

<body>
<div class="botones-acciones">
  <button id="nuevaColumna">Nueva Columna</button>
  <button id="nuevaTarea">Nueva Tarea</button>
</div>
<main class="tablero"></main>
<script>
const estadoGuardado = <?php echo $estado; ?>;
</script>
<script src="/modules/kanban/script/kanban.js"></script>
</body>
</html>