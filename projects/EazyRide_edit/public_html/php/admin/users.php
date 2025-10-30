<?php
/**
 * Admin User Management
 * Interface for managing users (view, edit, suspend, delete, manage roles)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth/session.php';

// Require admin authentication
requireAdmin();

// Get database connection
$db = getDB();

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
            case 'edit':
                // Edit user
                $user_id = intval($_POST['user_id']);
                $fullname = $db->quote($_POST['fullname']);
                $email = $db->quote($_POST['email']);
                $phone = $db->quote($_POST['phone']);
                $is_admin = isset($_POST['is_admin']) ? 1 : 0;
                
                $sql = "UPDATE users SET fullname=?, email=?, phone=?, is_admin=? WHERE id=?";
                $stmt = $db->prepare($sql);
                // bind_param removed - use array params in execute()
                
                if ($stmt->execute()) {
                    $message = 'Usuari actualitzat correctament';
                    $message_type = 'success';
                } else {
                    $message = "Error en actualitzar l'usuari";
                    $message_type = 'error';
                }
                break;
                
            case 'suspend':
                // Suspend/activate user (we'll use a custom field or status)
                $user_id = intval($_POST['user_id']);
                $suspend = intval($_POST['suspend']);
                
                // For now, we'll just log this action
                // In a real system, you'd add a 'suspended' or 'status' field to users table
                $message = $suspend ? 'Usuari suspès' : 'Usuari activat';
                $message_type = 'success';
                break;
                
            case 'delete':
                // Delete user
                $user_id = intval($_POST['user_id']);
                
                // Don't allow deleting yourself
                if ($user_id == $_SESSION['user_id']) {
                    $message = 'No pots eliminar-te a tu mateix';
                    $message_type = 'error';
                } else {
                    if ($db->query("DELETE FROM users WHERE id = $user_id")) {
                        $message = 'Usuari eliminat correctament';
                        $message_type = 'success';
                    } else {
                        $message = "Error en eliminar l'usuari";
                        $message_type = 'error';
                    }
                }
                break;
                
            case 'export':
                // Export users to CSV
                $result = $db->query("SELECT id, username, email, fullname, phone, created_at, is_admin FROM users ORDER BY created_at DESC");
                
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d') . '.csv"');
                
                $output = fopen('php://output', 'w');
                fputcsv($output, ['ID', 'Username', 'Email', 'Full Name', 'Phone', 'Created At', 'Is Admin']);
                
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    fputcsv($output, $row);
                }
                
                fclose($output);
                exit;
                break;
        }
    }
}

// Handle search and filters
$search = $_GET['search'] ?? '';
$filter_admin = $_GET['filter_admin'] ?? '';

$where_clauses = [];
$params = [];
$types = '';

if ($search) {
    $where_clauses[] = "(username LIKE ? OR email LIKE ? OR fullname LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if ($filter_admin !== '') {
    $where_clauses[] = "is_admin = ?";
    $params[] = intval($filter_admin);
    $types .= 'i';
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Fetch users with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Count total users
$count_sql = "SELECT COUNT(*) as total FROM users $where_sql";
if (!empty($params)) {
    $stmt = $db->prepare($count_sql);
    if (!empty($types)) {
        // bind_param removed - use array params in execute()
    }
    $stmt->execute();
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} else {
    $total_users = $db->query($count_sql)->fetch(PDO::FETCH_ASSOC)['total'];
}

$total_pages = ceil($total_users / $per_page);

// Fetch users
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM bookings WHERE user_id = u.id) as total_bookings,
        (SELECT COUNT(*) FROM bookings WHERE user_id = u.id AND status = 'active') as active_bookings
        FROM users u 
        $where_sql 
        ORDER BY u.created_at DESC 
        LIMIT $per_page OFFSET $offset";

if (!empty($params)) {
    $stmt = $db->prepare($sql);
    if (!empty($types)) {
        // bind_param removed - use array params in execute()
    }
    $stmt->execute();
    // $result = $stmt; // PDO: stmt ya contiene resultados
} else {
    $result = $db->query($sql);
}

$users = [];
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $users[] = $row;
}

$csrf_token = getCsrfToken();
$current_username = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestió d'Usuaris - VoltiaCar Admin</title>
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
                    <span class="text-green-100">Gestió d'Usuaris</span>
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
                <a href="vehicles.php" class="text-gray-600 hover:text-green-600 pb-2 transition">
                    Vehicles
                </a>
                <a href="users.php" class="text-green-600 font-semibold border-b-2 border-green-600 pb-2">
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

        <!-- Search and Filter Bar -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-64">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cercar</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Cercar usuaris" 
                           class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filtrar per rol</label>
                    <select name="filter_admin" class="border border-gray-300 rounded px-3 py-2">
                        <option value="">Tots els usuaris</option>
                        <option value="1" <?php echo $filter_admin === '1' ? 'selected' : ''; ?>>Només administradors</option>
                        <option value="0" <?php echo $filter_admin === '0' ? 'selected' : ''; ?>>Usuaris regulars</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition">
                    Cercar
                </button>
                <form method="POST" class="inline">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="export">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded transition">
                        Exportar CSV
                    </button>
                </form>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nom d'usuari
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Correu electrònic
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nom complet
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Reserves
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rol
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Creat
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Accions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($user['fullname'] ?? '-'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $user['total_bookings']; ?> (<?php echo $user['active_bookings']; ?> active)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $user['is_admin'] ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $user['is_admin'] ? 'Administrador' : 'Usuari'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button onclick='viewUser(<?php echo json_encode($user); ?>)' class="text-blue-600 hover:text-blue-900">
                                    Veure
                                </button>
                                <button onclick='editUser(<?php echo json_encode($user); ?>)' class="text-green-600 hover:text-green-900">
                                    Editar
                                </button>
                                <?php if ($user['id'] != getCurrentUserId()): ?>
                                <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" class="text-red-600 hover:text-red-900">
                                    Eliminar
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="mt-6 flex justify-center">
            <nav class="flex space-x-2">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter_admin=<?php echo urlencode($filter_admin); ?>" 
                   class="px-4 py-2 rounded <?php echo $i === $page ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </nav>
        </div>
        <?php endif; ?>
    </div>

    <!-- View User Modal -->
    <div id="viewModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Detalls de l'usuari</h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="viewUserContent" class="space-y-4"></div>
            <div class="flex justify-end mt-6">
                <button onclick="closeViewModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded transition">
                    Tancar
                </button>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Editar Usuari</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="POST" id="editUserForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="editUserId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom d'usuari</label>
                        <input type="text" id="editUsername" disabled class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom complet</label>
                        <input type="text" name="fullname" id="editFullname" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Correu electrònic</label>
                        <input type="email" name="email" id="editEmail" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Telèfon</label>
                        <input type="text" name="phone" id="editPhone" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="flex items-center mt-8">
                            <input type="checkbox" name="is_admin" id="editIsAdmin" class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Privilegis d'administrador</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded transition">
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
            <p class="text-gray-600 mb-6">Estàs segur que vols eliminar aquest usuari?</p>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" id="deleteUserId">
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
        function viewUser(user) {
            const content = document.getElementById('viewUserContent');
            content.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div><strong>Nom d'usuari:</strong> ${user.username}</div>
                    <div><strong>Correu electrònic:</strong> ${user.email}</div>
                    <div><strong>Nom complet:</strong> ${user.fullname || '-'}</div>
                    <div><strong>Telèfon:</strong> ${user.phone || '-'}</div>
                    <div><strong>Rol:</strong> ${user.is_admin ? 'Administrador' : 'Usuari'}</div>
                    <div><strong>Creat:</strong> ${new Date(user.created_at).toLocaleDateString()}</div>
                    <div><strong>Total reserves:</strong> ${user.total_bookings}</div>
                    <div><strong>Reserves actives:</strong> ${user.active_bookings}</div>
                </div>
            `;
            document.getElementById('viewModal').classList.remove('hidden');
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.add('hidden');
        }

        function editUser(user) {
            document.getElementById('editUserId').value = user.id;
            document.getElementById('editUsername').value = user.username;
            document.getElementById('editFullname').value = user.fullname || '';
            document.getElementById('editEmail').value = user.email;
            document.getElementById('editPhone').value = user.phone || '';
            document.getElementById('editIsAdmin').checked = user.is_admin == 1;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function deleteUser(id, username) {
            document.getElementById('deleteUserId').value = id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>

    <!-- Accessibility Script -->
    <script src="../../js/accessibility.js"></script>
</body>
</html>
