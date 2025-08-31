<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_entry'])) {
        $device_id = $_POST['device_id'];
        $pm25 = $_POST['pm25'];
        $pm10 = $_POST['pm10'];
        $co2 = $_POST['co2'];
        $co = $_POST['co'];
        $temperature = $_POST['temperature'];
        $humidity = $_POST['humidity'];
        $notes = $_POST['notes'];
        $entered_by = $_SESSION['user_id'];
        
        // Handle file upload
        $certificate_path = null;
        if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/certificates/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . basename($_FILES['certificate']['name']);
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['certificate']['tmp_name'], $target_path)) {
                $certificate_path = $target_path;
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO manual_entries (device_id, pm25, pm10, co2, co, temperature, humidity, notes, certificate_path, entered_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$device_id, $pm25, $pm10, $co2, $co, $temperature, $humidity, $notes, $certificate_path, $entered_by]);
        
        header('Location: manual_data.php');
        exit;
    }
}

// Get all devices for dropdown
$stmt = $pdo->query("SELECT id, device_id, name FROM devices ORDER BY name");
$devices = $stmt->fetchAll();

// Get all manual entries
$stmt = $pdo->query("
    SELECT m.*, d.device_id, d.name as device_name, u.username as entered_by_name 
    FROM manual_entries m 
    JOIN devices d ON m.device_id = d.id 
    JOIN users u ON m.entered_by = u.id 
    ORDER BY m.created_at DESC
");
$entries = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>Manual Data Entry & Calibration</h1>
    <p>Enter manual air quality readings and calibration data</p>
</div>

<div class="page-content">
    <div class="form-section">
        <h2>Add New Data Entry</h2>
        <form method="POST" enctype="multipart/form-data" class="card">
            <div class="form-row">
                <div class="form-group">
                    <label for="device_id">Select Sensor</label>
                    <select id="device_id" name="device_id" required>
                        <option value="">Choose sensor</option>
                        <?php foreach ($devices as $device): ?>
                        <option value="<?= $device['id'] ?>"><?= htmlspecialchars($device['name']) ?> (<?= $device['device_id'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="entry_date">Date</label>
                    <input type="date" id="entry_date" name="entry_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label for="entry_time">Time</label>
                    <input type="time" id="entry_time" name="entry_time" value="<?= date('H:i') ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="pm25">PM2.5 (μg/m³)</label>
                    <input type="number" id="pm25" name="pm25" step="0.1" value="0.0" required>
                </div>
                <div class="form-group">
                    <label for="pm10">PM10 (μg/m³)</label>
                    <input type="number" id="pm10" name="pm10" step="0.1" value="0.0" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="co2">CO₂ (ppm)</label>
                    <input type="number" id="co2" name="co2" step="0.1" value="400.0" required>
                </div>
                <div class="form-group">
                    <label for="co">CO (ppm)</label>
                    <input type="number" id="co" name="co" step="0.01" value="0.00" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="temperature">Temperature (°C)</label>
                    <input type="number" id="temperature" name="temperature" step="0.1" value="25.0" required>
                </div>
                <div class="form-group">
                    <label for="humidity">Humidity (%)</label>
                    <input type="number" id="humidity" name="humidity" step="0.1" value="60.0" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes">Calibration Notes</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Enter calibration details, reference standards used, environmental conditions, etc."></textarea>
            </div>
            
            <div class="form-group">
                <label for="certificate">Calibration Certificate</label>
                <input type="file" id="certificate" name="certificate" accept=".pdf,.doc,.docx,.jpg,.png">
                <small>Upload calibration certificate (PDF, Word, or image)</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="add_entry" class="btn btn-primary">Save Data Entry</button>
                <button type="button" class="btn btn-secondary">Auto-Calibrate</button>
            </div>
        </form>
    </div>
    
    <div class="list-section">
        <h2>Manual Data Entries</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Device</th>
                        <th>PM2.5</th>
                        <th>PM10</th>
                        <th>CO₂</th>
                        <th>CO</th>
                        <th>Temp</th>
                        <th>Humidity</th>
                        <th>Entered By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?= date('M j, H:i', strtotime($entry['created_at'])) ?></td>
                        <td><?= htmlspecialchars($entry['device_name']) ?></td>
                        <td><?= $entry['pm25'] ?> μg/m³</td>
                        <td><?= $entry['pm10'] ?> μg/m³</td>
                        <td><?= $entry['co2'] ?> ppm</td>
                        <td><?= $entry['co'] ?> ppm</td>
                        <td><?= $entry['temperature'] ?>°C</td>
                        <td><?= $entry['humidity'] ?>%</td>
                        <td><?= htmlspecialchars($entry['entered_by_name']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>