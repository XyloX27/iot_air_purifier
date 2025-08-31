<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_sensor'])) {
        $device_id = $_POST['device_id'];
        $name = $_POST['name'];
        $location_id = $_POST['location_id'];
        $sensor_type = $_POST['sensor_type'] ?? 'PM2.5';
        $interval = $_POST['interval'];
        $protocol = $_POST['protocol'];
        $device_status = $_POST['device_status'];
        $sensor_status = $_POST['sensor_status'];
        
        // First, create the device if it doesn't exist
        $stmt = $pdo->prepare("INSERT INTO devices (device_id, name, location_id, status) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), location_id = VALUES(location_id), status = VALUES(status)");
        $stmt->execute([$device_id, $name, $location_id, $device_status]);
        
        // Get the device ID
        $deviceId = $pdo->lastInsertId();
        if (!$deviceId) {
            $stmt = $pdo->prepare("SELECT id FROM devices WHERE device_id = ?");
            $stmt->execute([$device_id]);
            $deviceId = $stmt->fetchColumn();
        }
        
        // Then create the sensor
        $stmt = $pdo->prepare("INSERT INTO sensors (device_id, name, location_id, sensor_type, `interval`, protocol, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$deviceId, $name, $location_id, $sensor_type, $interval, $protocol, $sensor_status]);
        
        header('Location: sensors.php');
        exit;
    }
    
    if (isset($_POST['delete_sensor'])) {
        $sensorId = $_POST['sensor_id'];
        
        $stmt = $pdo->prepare("DELETE FROM sensors WHERE id = ?");
        $stmt->execute([$sensorId]);
        
        header('Location: sensors.php');
        exit;
    }
}

// Get all sensors with location and device information
$stmt = $pdo->query("
    SELECT s.*, d.device_id, d.name as device_name, l.name as location_name 
    FROM sensors s 
    LEFT JOIN devices d ON s.device_id = d.id 
    LEFT JOIN locations l ON s.location_id = l.id 
    ORDER BY s.created_at DESC
");

// Debug: Let's see what we're actually getting
$sensors = $stmt->fetchAll();
// Uncomment the line below to see the raw data structure
// echo "<pre>"; print_r($sensors); echo "</pre>";

// Get all locations for dropdown
$stmt = $pdo->query("SELECT id, name FROM locations ORDER BY name");
$locations = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>IoT Sensor Configuration</h1>
    <p>Manage your air quality monitoring sensors</p>
</div>

<div class="page-content">
    <div class="form-section">
        <h2>Add New Sensor</h2>
        <form method="POST" class="card">
            <div class="form-row">
                <div class="form-group">
                    <label for="device_id">Device ID</label>
                    <input type="text" id="device_id" name="device_id" required placeholder="e.g., AQ_DHK_001">
                </div>
                <div class="form-group">
                    <label for="name">Sensor Name</label>
                    <input type="text" id="name" name="name" required placeholder="e.g., Dhaka Sensor 1">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="location_id">Location</label>
                    <select id="location_id" name="location_id" required>
                        <option value="">Select Location</option>
                        <?php foreach ($locations as $location): ?>
                        <option value="<?= $location['id'] ?>"><?= htmlspecialchars($location['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="device_status">Device Status</label>
                    <select id="device_status" name="device_status" required>
                        <option value="online">Online</option>
                        <option value="offline">Offline</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="sensor_status">Sensor Status</label>
                    <select id="sensor_status" name="sensor_status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sensor_type">Sensor Type</label>
                    <select id="sensor_type" name="sensor_type" required>
                        <option value="PM2.5">PM2.5</option>
                        <option value="PM10">PM10</option>
                        <option value="CO2">CO₂</option>
                        <option value="CO">CO</option>
                        <option value="NO2">NO₂</option>
                        <option value="SO2">SO₂</option>
                        <option value="temperature">Temperature</option>
                        <option value="humidity">Humidity</option>
                    </select>
                </div>
            </div>
            

            
            <div class="form-row">
                <div class="form-group">
                    <label for="interval">Data Collection Interval (minutes)</label>
                    <input type="number" id="interval" name="interval" value="1" min="1" max="60">
                </div>
                <div class="form-group">
                    <label for="protocol">Communication Protocol</label>
                    <select id="protocol" name="protocol" required>
                        <option value="LoRaWAN">LoRaWAN</option>
                        <option value="WiFi">WiFi</option>
                        <option value="4G">4G</option>
                        <option value="5G">5G</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" name="add_sensor" class="btn btn-primary">Add Sensor</button>
        </form>
    </div>
    
    <div class="list-section">
        <h2>Your Sensors</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Device ID</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Sensors</th>
                        <th>Status</th>
                        <th>Protocol</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sensors as $sensor): ?>
                    <tr>
                        <td><?= htmlspecialchars($sensor['device_id'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($sensor['name']) ?></td>
                        <td><?= htmlspecialchars($sensor['location_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($sensor['sensor_type']) ?></td>
                        <td>
                            <?php if (!empty($sensor['status'])): ?>
                                <span class="status-badge <?= htmlspecialchars($sensor['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($sensor['status'])) ?>
                                </span>
                            <?php else: ?>
                                <span class="status-badge inactive">No Status</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($sensor['protocol'] ?? 'N/A') ?></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="sensor_id" value="<?= $sensor['id'] ?>">
                                <button type="submit" name="delete_sensor" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this sensor?')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>