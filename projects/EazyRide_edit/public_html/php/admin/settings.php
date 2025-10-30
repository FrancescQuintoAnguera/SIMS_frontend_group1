<?php
/**
 * Settings - VoltiaCar Admin
 * System configuration and settings
 */

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../pages/auth/login.html');
    exit;
}

$pageTitle = 'Configuració';
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - VoltiaCar Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../assets/css/accessibility.css">
    <script src="../assets/css/tailwind.config.js"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <img src="../assets/img/logo.png" alt="VoltiaCar Logo" class="h-10 w-10 rounded-full">
                        <span class="ml-3 text-xl font-bold text-gray-900">VoltiaCar Admin</span>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="index.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Tauler</a>
                        <a href="vehicles.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Vehicles</a>
                        <a href="users.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Usuaris</a>
                        <a href="settings.php" class="border-primary-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Configuració</a>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="../pages/dashboard/gestio.html" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">Tornar a l'App</a>
                    <button onclick="logout()" class="ml-3 bg-gray-300 text-gray-900 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">Tancar Sessió</button>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <h1 class="text-3xl font-bold text-gray-900 mb-6"><?php echo $pageTitle; ?></h1>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Pricing Settings -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Preus</h2>
                    <form id="pricingForm">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tarifa de Desbloqueig (€)</label>
                            <input type="number" step="0.01" value="0.50" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preu per Minut (€)</label>
                            <input type="number" step="0.01" value="0.30" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preu Premium Mensual (€)</label>
                            <input type="number" step="0.01" value="9.99" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        </div>
                        <button type="submit" class="w-full bg-primary-500 text-white px-4 py-2 rounded-lg hover:bg-primary-600 transition-colors font-semibold">
                            Guardar Preus
                        </button>
                    </form>
                </div>

                <!-- System Settings -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Sistema</h2>
                    <form id="systemForm">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Temps Màxim de Reserva (minuts)</label>
                            <input type="number" value="30" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bateria Mínima per Reservar (%)</label>
                            <input type="number" value="20" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" checked class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="ml-2 text-sm text-gray-700">Permetre registres nous</span>
                            </label>
                        </div>
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" checked class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="ml-2 text-sm text-gray-700">Mode manteniment</span>
                            </label>
                        </div>
                        <button type="submit" class="w-full bg-primary-500 text-white px-4 py-2 rounded-lg hover:bg-primary-600 transition-colors font-semibold">
                            Guardar Configuració
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/toast.js"></script>
    <script src="../assets/js/confirm-modal.js"></script>    
    <script>
        async function logout() {
            const confirmed = await showConfirm(
                'Estàs segur que vols tancar la sessió?',
                'Tancar sessió'
            );
            if (confirmed) {
                window.location.href = '../src/api/logout.php';
            }
        }

        document.getElementById('pricingForm').addEventListener('submit', (e) => {
            e.preventDefault();
            showToast(
                'Preus guardats correctament',
                "success"
            )
        });

        document.getElementById('systemForm').addEventListener('submit', (e) => {
            e.preventDefault();
            showToast(
                'Configuració guardada correctament',
                "success"
            )
        });
    </script>
</body>
</html>
