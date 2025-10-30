<?php
/**
 * Admin Dashboard
 * Displays statistics, charts, and quick actions for administrators
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth/session.php';

// Require admin authentication
requireAdmin();

// Get database connections
$db = getDB();
$mongodb = getMongoDB();

// Fetch statistics
$stats = [];

// Total users
$result = $db->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $result->fetch_assoc()['total'];

// Total vehicles (from MongoDB)
$stats['total_vehicles'] = $mongodb->cars->countDocuments();

// Active bookings
$result = $db->query("SELECT COUNT(*) as total FROM bookings WHERE status IN ('confirmed', 'active')");
$stats['active_bookings'] = $result->fetch_assoc()['total'];

// Total revenue (from payments)
$result = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments");
$stats['total_revenue'] = $result->fetch_assoc()['total'];

// Recent bookings (last 7 days)
$result = $db->query("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM bookings 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$booking_trends = [];
while ($row = $result->fetch_assoc()) {
    $booking_trends[] = $row;
}

// User growth (last 30 days)
$result = $db->query("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$user_growth = [];
while ($row = $result->fetch_assoc()) {
    $user_growth[] = $row;
}

// Vehicle usage statistics (from MongoDB)
$vehicle_stats = [];
$vehicles = $mongodb->cars->find();
foreach ($vehicles as $vehicle) {
    $status = $vehicle['status'] ?? 'unknown';
    if (!isset($vehicle_stats[$status])) {
        $vehicle_stats[$status] = 0;
    }
    $vehicle_stats[$status]++;
}

// Recent activity (last 10 bookings)
$result = $db->query("
    SELECT b.id, b.created_at, b.status, u.username, v.brand, v.model, v.plate
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN vehicles v ON b.vehicle_id = v.id
    ORDER BY b.created_at DESC
    LIMIT 10
");
$recent_activity = [];
while ($row = $result->fetch_assoc()) {
    $recent_activity[] = $row;
}


?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tauler d'Administració - VoltiaCar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../../css/accessibility.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-green-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold">VoltiaCar Admin</h1>
                    <span class="text-green-100">|</span>
                    <span class="text-green-100">Tauler</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span><?php echo getCurrentUsername(); ?></span>
                    <a href="../../index.php" class="bg-green-700 hover:bg-green-800 px-4 py-2 rounded transition">
                        Tornar al lloc
                    </a>
                    <a href="../../php/auth/logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition">
                        Tancar sessió
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Admin Menu -->
    <div class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex space-x-6 py-3">
                <a href="dashboard.php" class="text-green-600 font-semibold border-b-2 border-green-600 pb-2">
                    Tauler
                </a>
                <a href="vehicles.php" class="text-gray-600 hover:text-green-600 pb-2 transition">
                    Vehicles
                </a>
                <a href="users.php" class="text-gray-600 hover:text-green-600 pb-2 transition">
                    Usuaris
                </a>
                <a href="bookings.php" class="text-gray-600 hover:text-green-600 pb-2 transition">
                    Reserves
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total usuaris</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['total_users']); ?></p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Vehicles -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total vehicles</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['total_vehicles']); ?></p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Bookings -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Reserves actives</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['active_bookings']); ?></p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Ingressos totals</p>
                        <p class="text-3xl font-bold text-gray-800">€<?php echo number_format($stats['total_revenue'], 2); ?></p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Booking Trends Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Tendències de reserves</h2>
                <canvas id="bookingTrendsChart"></canvas>
            </div>

            <!-- Vehicle Status Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Estat dels vehicles</h2>
                <canvas id="vehicleStatusChart"></canvas>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Accions ràpides</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="vehicles.php?action=add" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-center transition">
                    Afegir vehicle
                </a>
                <a href="users.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-center transition">
                    Gestionar usuaris
                </a>
                <a href="bookings.php?status=pending" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-lg text-center transition">
                    Reserves pendents
                </a>
                <a href="bookings.php?action=report" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg text-center transition">
                    Generar informe
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Activitat recent</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID Reserva
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuari
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Vehicle
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estat
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recent_activity as $activity): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                #<?php echo $activity['id']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($activity['username']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($activity['brand'] . ' ' . $activity['model'] . ' (' . $activity['plate'] . ')'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php 
                                    echo match($activity['status']) {
                                        'confirmed' => 'bg-blue-100 text-blue-800',
                                        'active' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-gray-100 text-gray-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-yellow-100 text-yellow-800'
                                    };
                                    ?>">
                                    <?php echo match($activity['status']) { 'pending' => 'Pendent', 'confirmed' => 'Confirmat', 'active' => 'Actiu', 'completed' => 'Completat', 'cancelled' => 'Cancel·lat', default => $activity['status'] }; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Booking Trends Chart
        const bookingTrendsCtx = document.getElementById('bookingTrendsChart').getContext('2d');
        const bookingTrendsData = <?php echo json_encode($booking_trends); ?>;
        
        new Chart(bookingTrendsCtx, {
            type: 'line',
            data: {
                labels: bookingTrendsData.map(item => item.date),
                datasets: [{
                    label: 'Reserves',
                    data: bookingTrendsData.map(item => item.count),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Vehicle Status Chart
        const vehicleStatusCtx = document.getElementById('vehicleStatusChart').getContext('2d');
        const vehicleStatusData = <?php echo json_encode($vehicle_stats); ?>;
        
        new Chart(vehicleStatusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(vehicleStatusData).map(status => status.charAt(0).toUpperCase() + status.slice(1)),
                datasets: [{
                    data: Object.values(vehicleStatusData),
                    backgroundColor: [
                        'rgb(34, 197, 94)',   // available - green
                        'rgb(239, 68, 68)',   // in_use - red
                        'rgb(251, 191, 36)',  // charging - yellow
                        'rgb(156, 163, 175)', // maintenance - gray
                        'rgb(59, 130, 246)'   // other - blue
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

    <!-- Accessibility Script -->
    <script src="../../js/accessibility.js"></script>
</body>
</html>
