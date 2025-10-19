<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eazy Ride</title>
    <link rel="stylesheet" href="/common/style/common.css">
    <link rel="stylesheet" href="/common/navBar/styles/navbar.css">
    
    <!-- Cargar el Web Component ANTES de usarlo -->
    <script src="/common/sidebar/scripts/sidebar.js"></script>
</head>
<body>
    <?php include_once __DIR__ . '/common/navBar/template/navbar.php'; ?>
    <?php include_once __DIR__ . '/common/sidebar/templates/sidebar.php'; ?>
    
    <main id="app">Cargando...</main>
    
    <script src="/router/router.js"></script>
    <script src="/common/scripts/main.js"></script>
</body>
</html>