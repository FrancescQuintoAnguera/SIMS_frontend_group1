<?php
/**
 * Admin Dashboard - VoltiaCar
 * Main admin panel with statistics and quick actions
 */

session_start();


// Check authentication (simplified - implement proper auth)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../pages/auth/login.html');
    exit;
}

$pageTitle = 'Tauler d\'Administració';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - VoltiaCar Admin</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../assets/css/accessibility.css">
    
    <!-- Tailwind Config -->
    <script src="../assets/css/tailwind.config.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <img src="../assets/img/logo.png" alt="VoltiaCar Logo" class="h-10 w-10 rounded-full">
                        <span class="ml-3 text-xl font-bold text-gray-900">VoltiaCar Admin</span>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="index.php" class="border-primary-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Tauler
                        </a>
                        <a href="vehicles.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Vehicles
                        </a>
                        <a href="users.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Usuaris
                        </a>
                        <a href="settings.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Configuració
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="../pages/dashboard/gestio.html" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                        Tornar a l'App
                    </a>
                    <button onclick="logout()" class="ml-3 bg-gray-300 text-gray-900 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                        Tancar Sessió
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="px-4 py-6 sm:px-0">
            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo $pageTitle; ?></h1>
            <p class="text-gray-600">Benvingut al panell d'administració de VoltiaCar</p>
        </div>

        <!-- Statistics Cards -->
        <div class="px-4 py-6 sm:px-0">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Users -->
                <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-primary-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Usuaris</dt>
                                    <dd class="text-3xl font-bold text-gray-900" id="totalUsers">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Vehicles -->
                <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-secondary-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Vehicles</dt>
                                    <dd class="text-3xl font-bold text-gray-900" id="totalVehicles">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Trips -->
                <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Viatges Actius</dt>
                                    <dd class="text-3xl font-bold text-gray-900" id="activeTrips">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Today -->
                <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Ingressos Avui</dt>
                                    <dd class="text-3xl font-bold text-gray-900" id="revenueToday">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="px-4 py-6 sm:px-0">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Accions Ràpides</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="vehicles.php?action=add" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-primary-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Afegir Vehicle</h3>
                            <p class="text-sm text-gray-500">Registrar nou vehicle a la flota</p>
                        </div>
                    </div>
                </a>

                <a href="users.php?action=add" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-secondary-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Afegir Usuari</h3>
                            <p class="text-sm text-gray-500">Crear nou compte d'usuari</p>
                        </div>
                    </div>
                </a>

                <a href="settings.php" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gray-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Configuració</h3>
                            <p class="text-sm text-gray-500">Gestionar configuració del sistema</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="px-4 py-6 sm:px-0">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Activitat Recent</h2>
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuari</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acció</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        </tr>
                    </thead>
                    <tbody id="recentActivity" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Carregant activitat...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="../assets/js/main.js"></script>
    <script src="/js/toast.js"></script>
    <script src="/js/confirm-modal.js"></script>
    <script>
        // Load dashboard statistics
        async function loadStatistics() {
            try {
                // Simulate API call - replace with actual endpoint
                const stats = {
                    totalUsers: 156,
                    totalVehicles: 24,
                    activeTrips: 8,
                    revenueToday: '€342.50'
                };
                
                document.getElementById('totalUsers').textContent = stats.totalUsers;
                document.getElementById('totalVehicles').textContent = stats.totalVehicles;
                document.getElementById('activeTrips').textContent = stats.activeTrips;
                document.getElementById('revenueToday').textContent = stats.revenueToday;
            } catch (error) {
                console.error('Error loading statistics:', error);
            }
        }

        // Load recent activity
        async function loadRecentActivity() {
            try {
                // Simulate API call - replace with actual endpoint
                const activities = [
                    { user: 'Joan Garcia', action: 'Viatge iniciat', vehicle: 'AB 123 CD', date: '10:45' },
                    { user: 'Maria López', action: 'Temps comprat', vehicle: '-', date: '10:30' },
                    { user: 'Pere Martí', action: 'Viatge finalitzat', vehicle: 'EF 456 GH', date: '10:15' },
                    { user: 'Anna Soler', action: 'Registre nou', vehicle: '-', date: '10:00' },
                ];
                
                const tbody = document.getElementById('recentActivity');
                tbody.innerHTML = activities.map(activity => `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${activity.user}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${activity.action}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${activity.vehicle}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${activity.date}</td>
                    </tr>
                `).join('');
            } catch (error) {
                console.error('Error loading recent activity:', error);
            }
        }

        // Logout function
        async function logout() {
            const confirmed = await showConfirm(
                'Estàs segur que vols tancar la sessió?',
                'Tancar sessió'
            );
            if (confirmed) {
                window.location.href = '../src/api/logout.php';
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadStatistics();
            loadRecentActivity();
        });
    </script>
</body>
</html>
