<?php 
$error = $error ?? null;
$success = $success ?? null;
$chargingPoints = $chargingPoints ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - CRUD Points</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    
    <link rel="stylesheet" href="view/admin/admin.css">
</head>
<body>
    <h1>🔌 Charging Points Administration</h1>

    <?php if (isset($success)): ?>
        <p class="message success"><?php echo $success; ?></p>
    <?php elseif (isset($error)): ?>
        <p class="message error"><?php echo $error; ?></p>
    <?php endif; ?>

    <div class="crud-container">
        
        <form id="pointForm" class="form-panel" method="POST" action="index.php?dir=admin">
            <h2>➕ Add New Point</h2>
            <input type="hidden" name="action" value="save_station">

            <label for="name">Site Name:</label>
            <input type="text" id="name" name="name" required>
            
            <label for="latitude">Latitude (Y):</label>
            <input type="text" id="topCoord" name="latitude" readonly required> 
            
            <label for="longitude">Longitude (X):</label>
            <input type="text" id="leftCoord" name="longitude" readonly required> 
            
            <p class="instruction">
                **Instruction:** Click on the map on the right to set the coordinates.
            </p>

            <button type="submit">Save New Point</button>
        </form>

        <div class="map-panel">
            <h2>Map Preview</h2>
            <div class="map-container" id="adminMap">
            </div>
        </div>

    </div>

    <hr>

    <div class="management-container">
        <h2>📋 Manage Existing Points</h2>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($chargingPoints)): ?>
                    <tr><td colspan="6">No charging points found.</td></tr>
                <?php else: ?>
                    <?php foreach ($chargingPoints as $point): ?>
                        <tr>
                            <form method="POST" action="index.php?dir=admin" style="display:contents;">
                                <input type="hidden" name="action" value="update_point">
                                <input type="hidden" name="edit_id" value="<?php echo $point['id']; ?>">
                                
                                <td>#<?php echo $point['id']; ?></td>
                                
                                <td><input type="text" name="edit_name" value="<?php echo htmlspecialchars($point['name'] ?? ''); ?>" required></td>
                                
                                <td><input type="text" name="edit_latitude" value="<?php echo $point['latitude'] ?? ''; ?>" required></td>
                                
                                <td><input type="text" name="edit_longitude" value="<?php echo $point['longitude'] ?? ''; ?>" required></td>
                                
                                <td>
                                    <select name="edit_status" required>
                                        <option value="active" <?php echo (isset($point['status']) && $point['status'] == 'active') ? 'selected' : ''; ?>>Active (Orange)</option>
                                        <option value="inactive" <?php echo (isset($point['status']) && $point['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive (Green)</option>
                                        <option value="broken" <?php echo (isset($point['status']) && $point['status'] == 'broken') ? 'selected' : ''; ?>>Broken (Red)</option>
                                    </select>
                                </td>
                                
                                <td>
                                    <button type="submit" class="btn-update">Update</button>
                                </form>
                                
                                <form method="POST" action="index.php?dir=admin" style="display:inline-block;">
                                    <input type="hidden" name="action" value="delete_point">
                                    <input type="hidden" name="point_id" value="<?php echo $point['id']; ?>">
                                    <button type="submit" class="btn-delete" onclick="return confirm('Are you sure you want to delete point #<?php echo $point['id']; ?>?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="view/admin/admin_map_click.js"></script> 
</body>
</html>