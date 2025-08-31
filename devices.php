<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Handle device actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_device'])) {
        $name = $_POST['name'];
        $location = $_POST['location'];
        $status = $_POST['status'];
        
        $stmt = $pdo->prepare("INSERT INTO devices (name, location, status) VALUES (?, ?, ?)");
        $stmt->execute([$name, $location, $status]);
        
        header('Location: devices.php');
        exit;
    }
    
    if (isset($_POST['delete_device'])) {
        $deviceId = $_POST['device_id'];
        
        $stmt = $pdo->prepare("DELETE FROM devices WHERE id = ?");
        $stmt->execute([$deviceId]);
        
        header('Location: devices.php');
        exit;
    }
}

// Get all devices
$stmt = $pdo->query("SELECT * FROM devices ORDER BY created_at DESC");
$devices = $stmt->fetchAll();
?>

<div class="devices-header">
    <h1>Device Management</h1>
    <p>Manage your air purifier devices</p>
</div>

<div class="devices-content">
    <div class="add-device-form">
        <h2>Add New Device</h2>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Device Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" required>
                </div>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
            <button type="submit" name="add_device">Add Device</button>
        </form>
    </div>
    
    <div class="devices-list">
        <h2>Your Devices</h2>
        <div class="devices-grid">
            <?php foreach ($devices as $device): ?>
            <div class="device-card">
                <div class="device-header">
                    <h3><?= htmlspecialchars($device['name']) ?></h3>
                    <span class="status-badge <?= $device['status'] ?>">
                        <?= ucfirst($device['status']) ?>
                    </span>
                </div>
                <div class="device-info">
                    <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($device['location']) ?></p>
                    <p><i class="fas fa-clock"></i> Last active: <?= $device['last_active'] ? date('M j, H:i', strtotime($device['last_active'])) : 'N/A' ?></p>
                    <p><i class="fas fa-calendar"></i> Added: <?= date('M j, Y', strtotime($device['created_at'])) ?></p>
                </div>
                <div class="device-actions">
                    <form method="POST" class="delete-form">
                        <input type="hidden" name="device_id" value="<?= $device['id'] ?>">
                        <button type="submit" name="delete_device" class="btn-danger" onclick="return confirm('Are you sure you want to delete this device?')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>