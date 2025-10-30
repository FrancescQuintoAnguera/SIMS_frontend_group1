<?php
/**
 * Admin Vehicle Management
 * Interface for managing vehicles (add, edit, delete, set availability)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth/session.php';

// Require admin authentication
requireAdmin();

// Get database connections
$db = getDB();
$mongodb = getMongoDB();

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $message = 'Token CSRF invàlid';
        $message_type = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'add':
                // Add new vehicle
                $plate = $db->quote($_POST['plate']);
                $brand = $db->quote($_POST['brand']);
                $model = $db->quote($_POST['model']);
                $year = intval($_POST['year']);
                $battery_level = intval($_POST['battery_level']);
                $latitude = floatval($_POST['latitude']);
                $longitude = floatval($_POST['longitude']);
                $status = $db->quote($_POST['status']);
                $vehicle_type = $db->quote($_POST['vehicle_type']);
                $is_accessible = isset($_POST['is_accessible']) ? 1 : 0;
                $price_per_minute = floatval($_POST['price_per_minute']);
                $image_url = $db->quote($_POST['image_url'] ?? '');
                
                // Accessibility features
                $accessibility_features = [];
                if (isset($_POST['accessibility_features'])) {
                    $accessibility_features = $_POST['accessibility_features'];
                }
                $accessibility_json = json_encode($accessibility_features);
                
                // Insert into MariaDB
                $sql = "INSERT INTO vehicles (plate, brand, model, year, battery_level, latitude, longitude, status, vehicle_type, is_accessible, accessibility_features, price_per_minute, image_url) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                
                if ($stmt->execute([$plate, $brand, $model, $year, $battery_level, $latitude, $longitude, $status, $vehicle_type, $is_accessible, $accessibility_json, $price_per_minute, $image_url])) {
                    // Also add to MongoDB
                    $mongodb->cars->insertOne([
                        'license_plate' => $plate,
                        'brand' => $brand,
                        'model' => $model,
                        'year' => $year,
                        'status' => $status,
                        'battery_level' => $battery_level,
                        'location' => [
                            'type' => 'Point',
                            'coordinates' => [$longitude, $latitude]
                        ],
                        'location_name' => '',
                        'vehicle_type' => $vehicle_type,
                        'is_accessible' => $is_accessible,
                        'accessibility_features' => $accessibility_features,
                        'price_per_minute' => $price_per_minute,
                        'image_url' => $image_url,
                        'last_updated' => new MongoDB\BSON\UTCDateTime()
                    ]);
                    
                    $message = 'Vehicle afegit correctament';
                    $message_type = 'success';
                } else {
                    $message = 'Error en afegir el vehicle';
                    $message_type = 'error';
                }
                break;
                
            case 'edit':
                // Edit existing vehicle
                $id = intval($_POST['vehicle_id']);
                $plate = $db->quote($_POST['plate']);
                $brand = $db->quote($_POST['brand']);
                $model = $db->quote($_POST['model']);
                $year = intval($_POST['year']);
                $battery_level = intval($_POST['battery_level']);
                $latitude = floatval($_POST['latitude']);
                $longitude = floatval($_POST['longitude']);
                $status = $db->quote($_POST['status']);
                $vehicle_type = $db->quote($_POST['vehicle_type']);
                $is_accessible = isset($_POST['is_accessible']) ? 1 : 0;
                $price_per_minute = floatval($_POST['price_per_minute']);
                $image_url = $db->quote($_POST['image_url'] ?? '');
                
                // Accessibility features
                $accessibility_features = [];
                if (isset($_POST['accessibility_features'])) {
                    $accessibility_features = $_POST['accessibility_features'];
                }
                $accessibility_json = json_encode($accessibility_features);
                
                // Update MariaDB
                $sql = "UPDATE vehicles SET brand=?, model=?, year=?, battery_level=?, latitude=?, longitude=?, status=?, vehicle_type=?, is_accessible=?, accessibility_features=?, price_per_minute=?, image_url=? WHERE id=?";
                $stmt = $db->prepare($sql);
                
                if ($stmt->execute([$brand, $model, $year, $battery_level, $latitude, $longitude, $status, $vehicle_type, $is_accessible, $accessibility_json, $price_per_minute, $image_url, $id])) {
                    // Also update MongoDB
                    $mongodb->cars->updateOne(
                        ['license_plate' => $plate],
                        ['$set' => [
                            'brand' => $brand,
                            'model' => $model,
                            'year' => $year,
                            'status' => $status,
                            'battery_level' => $battery_level,
                            'location' => [
                                'type' => 'Point',
                                'coordinates' => [$longitude, $latitude]
                            ],
                            'vehicle_type' => $vehicle_type,
                            'is_accessible' => $is_accessible,
                            'accessibility_features' => $accessibility_features,
                            'price_per_minute' => $price_per_minute,
                            'image_url' => $image_url,
                            'last_updated' => new MongoDB\BSON\UTCDateTime()
                        ]]
                    );
                    
                    $message = 'Vehicle actualitzat correctament';
                    $message_type = 'success';
                } else {
                    $message = 'Error en actualitzar el vehicle';
                    $message_type = 'error';
                }
                break;
                
            case 'delete':
                // Delete vehicle
                $id = intval($_POST['vehicle_id']);
                
                // Get plate before deleting
                $result = $db->query("SELECT plate FROM vehicles WHERE id = $id");
                if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $plate = $row['plate'];
                    
                    // Delete from MariaDB
                    if ($db->query("DELETE FROM vehicles WHERE id = $id")) {
                        // Also delete from MongoDB
                        $mongodb->cars->deleteOne(['license_plate' => $plate]);
                        
                        $message = 'Vehicle eliminat correctament';
                        $message_type = 'success';
                    } else {
                        $message = 'Error en eliminar el vehicle';
                        $message_type = 'error';
                    }
                }
                break;
                
            case 'bulk_status':
                // Bulk status update
                $vehicle_ids = $_POST['vehicle_ids'] ?? [];
                $new_status = $db->quote($_POST['bulk_status']);
                
                if (!empty($vehicle_ids)) {
                    $ids = implode(',', array_map('intval', $vehicle_ids));
                    
                    // Get plates for MongoDB update
                    $result = $db->query("SELECT plate FROM vehicles WHERE id IN ($ids)");
                    $plates = [];
                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        $plates[] = $row['plate'];
                    }
                    
                    // Update MariaDB
                    if ($db->query("UPDATE vehicles SET status = '$new_status' WHERE id IN ($ids)")) {
                        // Update MongoDB
                        $mongodb->cars->updateMany(
                            ['license_plate' => ['$in' => $plates]],
                            ['$set' => ['status' => $new_status, 'last_updated' => new MongoDB\BSON\UTCDateTime()]]
                        );
                        
                        $message = 'Vehicles actualitzats correctament';
                        $message_type = 'success';
                    } else {
                        $message = 'Error en actualitzar els vehicles';
                        $message_type = 'error';
                    }
                }
                break;
        }
    }
}

// Fetch all vehicles
$vehicles = [];
$result = $db->query("SELECT * FROM vehicles ORDER BY created_at DESC");
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $vehicles[] = $row;
}

$csrf_token = getCsrfToken();
$current_username = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestió de Vehicles - VoltiaCar Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                    <span class="text-green-100">Gestió de Vehicles</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span><?php echo htmlspecialchars($current_username); ?></span>
                    <a href="../../index.php" class="bg-green-700 hover:bg-green-800 px-4 py-2 rounded transition">
                        Tornar al lloc
                    </a>
                    <a href="../auth/logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition">
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
                <a href="dashboard.php" class="text-gray-600 hover:text-green-600 pb-2 transition">
                    Tauler
                </a>
                <a href="vehicles.php" class="text-green-600 font-semibold border-b-2 border-green-600 pb-2">
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
        <!-- Messages -->
        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Actions Bar -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Vehicles</h2>
                <button onclick="showAddVehicleModal()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition">
                    Afegir Vehicle
                </button>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6" id="bulkActionsBar" style="display: none;">
            <form method="POST" class="flex items-center space-x-4" onsubmit="return submitBulkAction(event)">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="bulk_status">
                <span class="text-gray-700" id="selectedCount">0 selected</span>
                <select name="bulk_status" class="border border-gray-300 rounded px-4 py-2">
                    <option value="available">Disponible</option>
                    <option value="in_use">En ús</option>
                    <option value="maintenance">Manteniment</option>
                    <option value="out_of_service">Fora de servei</option>
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
                    Actualitzar estat
                </button>
            </form>
        </div>

        <!-- Vehicles Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Matrícula
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Vehicle
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Bateria
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estat
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Preu
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Accessible
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Accions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($vehicles as $vehicle): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <input type="checkbox" class="vehicle-checkbox" value="<?php echo $vehicle['id']; ?>" onchange="updateBulkActions()">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($vehicle['plate']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model'] . ' (' . $vehicle['year'] . ')'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo $vehicle['battery_level']; ?>%"></div>
                                    </div>
                                    <span><?php echo $vehicle['battery_level']; ?>%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php 
                                    echo match($vehicle['status']) {
                                        'available' => 'bg-green-100 text-green-800',
                                        'in_use' => 'bg-blue-100 text-blue-800',
                                        'maintenance' => 'bg-yellow-100 text-yellow-800',
                                        'out_of_service' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                    ?>">
                                    <?php 
                                    echo match($vehicle['status']) {
                                        'available' => 'Disponible',
                                        'in_use' => 'En ús',
                                        'maintenance' => 'Manteniment',
                                        'out_of_service' => 'Fora de servei',
                                        default => $vehicle['status']
                                    };
                                    ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                €<?php echo number_format($vehicle['price_per_minute'], 2); ?>/min
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $vehicle['is_accessible'] ? '✓' : '✗'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button onclick='editVehicle(<?php echo json_encode($vehicle); ?>)' class="text-blue-600 hover:text-blue-900">
                                    Editar
                                </button>
                                <button onclick="deleteVehicle(<?php echo $vehicle['id']; ?>, '<?php echo htmlspecialchars($vehicle['plate']); ?>')" class="text-red-600 hover:text-red-900">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Vehicle Modal -->
    <div id="vehicleModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">Afegir Vehicle</h3>
                <button onclick="closeVehicleModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="POST" id="vehicleForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="vehicle_id" id="vehicleId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Matrícula</label>
                        <input type="text" name="plate" id="plate" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Marca</label>
                        <input type="text" name="brand" id="brand" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                        <input type="text" name="model" id="model" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Any</label>
                        <input type="number" name="year" id="year" required min="2000" max="2030" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nivell de bateria (%)</label>
                        <input type="number" name="battery_level" id="battery_level" required min="0" max="100" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estat</label>
                        <select name="status" id="status" required class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="available">Disponible</option>
                            <option value="in_use">En ús</option>
                            <option value="maintenance">Manteniment</option>
                            <option value="out_of_service">Fora de servei</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Latitud</label>
                        <input type="number" name="latitude" id="latitude" required step="0.00000001" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Longitud</label>
                        <input type="number" name="longitude" id="longitude" required step="0.00000001" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipus de vehicle</label>
                        <input type="text" name="vehicle_type" id="vehicle_type" value="electric" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preu per minut (€)</label>
                        <input type="number" name="price_per_minute" id="price_per_minute" required step="0.01" min="0" value="0.30" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">URL imatge</label>
                        <input type="text" name="image_url" id="image_url" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_accessible" id="is_accessible" class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Vehicle accessible</span>
                        </label>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Característiques d'accessibilitat</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="accessibility_features[]" value="wheelchair_ramp" class="mr-2 accessibility-feature">
                                <span class="text-sm">Rampa per cadira de rodes</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="accessibility_features[]" value="hand_controls" class="mr-2 accessibility-feature">
                                <span class="text-sm">Controls manuals</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="accessibility_features[]" value="swivel_seat" class="mr-2 accessibility-feature">
                                <span class="text-sm">Seient giratori</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeVehicleModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded transition">
                        Cancel·lar
                    </button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded transition">
                        Desar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirmar eliminació</h3>
            <p class="text-gray-600 mb-6">Estàs segur que vols eliminar aquest vehicle?</p>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="vehicle_id" id="deleteVehicleId">
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeDeleteModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded transition">
                        Cancel·lar
                    </button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition">
                        Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddVehicleModal() {
            document.getElementById('modalTitle').textContent = 'Afegir Vehicle';
            document.getElementById('formAction').value = 'add';
            document.getElementById('vehicleForm').reset();
            document.getElementById('vehicleModal').classList.remove('hidden');
        }

        function editVehicle(vehicle) {
            document.getElementById('modalTitle').textContent = 'Editar Vehicle';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('vehicleId').value = vehicle.id;
            document.getElementById('plate').value = vehicle.plate;
            document.getElementById('brand').value = vehicle.brand;
            document.getElementById('model').value = vehicle.model;
            document.getElementById('year').value = vehicle.year;
            document.getElementById('battery_level').value = vehicle.battery_level;
            document.getElementById('latitude').value = vehicle.latitude;
            document.getElementById('longitude').value = vehicle.longitude;
            document.getElementById('status').value = vehicle.status;
            document.getElementById('vehicle_type').value = vehicle.vehicle_type || 'electric';
            document.getElementById('price_per_minute').value = vehicle.price_per_minute;
            document.getElementById('image_url').value = vehicle.image_url || '';
            document.getElementById('is_accessible').checked = vehicle.is_accessible == 1;
            
            // Handle accessibility features
            const features = vehicle.accessibility_features ? JSON.parse(vehicle.accessibility_features) : [];
            document.querySelectorAll('.accessibility-feature').forEach(checkbox => {
                checkbox.checked = features.includes(checkbox.value);
            });
            
            document.getElementById('vehicleModal').classList.remove('hidden');
        }

        function closeVehicleModal() {
            document.getElementById('vehicleModal').classList.add('hidden');
        }

        function deleteVehicle(id, plate) {
            document.getElementById('deleteVehicleId').value = id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function toggleSelectAll(checkbox) {
            document.querySelectorAll('.vehicle-checkbox').forEach(cb => {
                cb.checked = checkbox.checked;
            });
            updateBulkActions();
        }

        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.vehicle-checkbox:checked');
            const count = checkboxes.length;
            const bulkBar = document.getElementById('bulkActionsBar');
            const selectedCount = document.getElementById('selectedCount');
            
            if (count > 0) {
                bulkBar.style.display = 'block';
                selectedCount.textContent = count + ' selected';
            } else {
                bulkBar.style.display = 'none';
            }
        }

        function submitBulkAction(event) {
            const checkboxes = document.querySelectorAll('.vehicle-checkbox:checked');
            const ids = Array.from(checkboxes).map(cb => cb.value);
            
            if (ids.length === 0) {
                event.preventDefault();
                return false;
            }
            
            // Add hidden inputs for each selected ID
            const form = event.target;
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'vehicle_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            return true;
        }
    </script>

    <!-- Accessibility Script -->
    <script src="../../js/accessibility.js"></script>
</body>
</html>
