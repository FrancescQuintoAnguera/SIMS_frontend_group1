<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eazy Ride</title>
    <link rel="stylesheet" href="/common/style/common.css">
    <link rel="stylesheet" href="/common/modules/navBar/styles/navbar.css">
    <link rel="stylesheet" href="/common/modules/footer/styles/footer.css">
    
    <script src="/common/modules/sidebar/scripts/sidebar.js"></script>
    
</head>
<body>
    <header>
        <?php include_once __DIR__ . '/common/modules/navBar/template/navbar.php'; ?>
        <?php include_once __DIR__ . '/common/modules/sidebar/templates/sidebar.php'; ?>
    </header>
    
    <main id="app">Cargando...</main>
    
    <footer>
        <?php include_once __DIR__ . '/common/modules/footer/template/footer.php';?>
    </footer>


    <script type="module" src="/auth/auth.js"></script>
    <script type="module" src="/router/router.js"></script>
    <script type="module" src="/common/scripts/main.js"></script>
</body>
</html>