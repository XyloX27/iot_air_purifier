<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_alert'])) {
        $location_id = $_POST['location_id'];
        $pollutant = $_POST['pollutant'];
        $moderate_level = $_POST['moderate_level'];
        $unhealthy_level = $_POST['unhealthy_level'];
        $hazardous_level = $_POST['hazardous_level'];
        $emergency_level = $_POST['emergency_level'];
        $delivery_methods = isset($_POST['delivery_methods']) ? json_encode($_POST['delivery_methods']) : '[]';
        
        $stmt = $pdo->prepare("INSERT INTO alerts (location_id, pollutant, moderate_level, unhealthy_level, hazardous_level, emergency_level, delivery_methods) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$location_id, $pollutant, $moderate_level, $unhealthy_level, $hazardous_level, $emergency_level, $delivery_methods]);
        
        header('Location: alerts.php');
        exit;
    }
    
    if (isset($_POST['delete_alert'])) {
        $alertId = $_POST['alert_id'];
        
        $stmt = $pdo->prepare("DELETE FROM alerts WHERE id = ?");
        $stmt->execute([$alertId]);
        
        header('Location: alerts.php');
        exit;
    }
}

// Get all alerts with location information
$stmt = $pdo->query("
    SELECT a.*, l.name as location_name 
    FROM alerts a 
    JOIN locations l ON a.location_id = l.id 
    ORDER BY a.created_at DESC
");
$alerts = $stmt->fetchAll();

// Get all locations for dropdown
$stmt = $pdo->query("SELECT id, name FROM locations ORDER BY name");
$locations = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>Alert Threshold Configuration</h1>
    <p>Configure air quality alert thresholds and delivery methods</p>
</div>

<div class="page-content">
    <div class="form-section">
        <h2>Add New Alert Configuration</h2>
        <form method="POST" class="card">
            <div class="form-row">
                <div class="form-group">
                    <label for="location_id">Select Location</label>
                    <select id="location_id" name="location_id" required>
                        <option value="">Choose location</option>
                        <?php foreach ($locations as $location): ?>
                        <option value="<?= $location['id'] ?>"><?= htmlspecialchars($location['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pollutant">Pollutant Type</label>
                    <select id="pollutant" name="pollutant" required>
                        <option value="PM2.5">PM2.5</option>
                        <option value="PM10">PM10</option>
                        <option value="CO2">CO₂</option>
                        <option value="CO">CO</option>
                        <option value="NO2">NO₂</option>
                        <option value="SO2">SO₂</option>
                        <option value="AQI">AQI</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="moderate_level">Moderate Level (Yellow)</label>
                    <input type="number" id="moderate_level" name="moderate_level" step="0.1" value="50.0" required>
                </div>
                <div class="form-group">
                    <label for="unhealthy_level">Unhealthy Level (Orange)</label>
                    <input type="number" id="unhealthy_level" name="unhealthy_level" step="0.1" value="100.0" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="hazardous_level">Hazardous Level (Red)</label>
                    <input type="number" id="hazardous_level" name="hazardous_level" step="0.1" value="200.0" required>
                </div>
                <div class="form-group">
                    <label for="emergency_level">Emergency Level (Purple)</label>
                    <input type="number" id="emergency_level" name="emergency_level" step="0.1" value="300.0" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Alert Delivery Methods</label>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="delivery_methods[]" value="Mobile Push" checked> Mobile Push Notification</label>
                    <label><input type="checkbox" name="delivery_methods[]" value="SMS"> SMS Alert</label>
                    <label><input type="checkbox" name="delivery_methods[]" value="Email"> Email Alert</label>
                    <label><input type="checkbox" name="delivery_methods[]" value="Social Media"> Social Media Post</label>
                </div>
            </div>
            
            <div class="form-group">
                <div class="alert-preview">
                    <h4>Preview:</h4>
                    <p>When <span id="preview-pollutant">PM2.5</span> exceeds <span id="preview-threshold">100.0</span> μg/m³ in <span id="preview-location">selected location</span>, send alerts to subscribed users via <span id="preview-methods">Mobile Push, Email</span>.</p>
                </div>
            </div>
            
            <button type="submit" name="add_alert" class="btn btn-primary">Save Alert Settings</button>
            <button type="button" class="btn btn-secondary">Send Test Alert</button>
        </form>
    </div>
    
    <div class="list-section">
        <h2>Your Alert Configurations</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Pollutant</th>
                        <th>Moderate</th>
                        <th>Unhealthy</th>
                        <th>Hazardous</th>
                        <th>Emergency</th>
                        <th>Delivery Methods</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alerts as $alert): 
                        $methods = json_decode($alert['delivery_methods'], true);
                        $methodsText = is_array($methods) ? implode(', ', $methods) : $alert['delivery_methods'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($alert['location_name']) ?></td>
                        <td><?= $alert['pollutant'] ?></td>
                        <td><?= $alert['moderate_level'] ?></td>
                        <td><?= $alert['unhealthy_level'] ?></td>
                        <td><?= $alert['hazardous_level'] ?></td>
                        <td><?= $alert['emergency_level'] ?></td>
                        <td><?= htmlspecialchars($methodsText) ?></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="alert_id" value="<?= $alert['id'] ?>">
                                <button type="submit" name="delete_alert" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this alert configuration?')">
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

<script>
// Update preview as user changes values
document.addEventListener('DOMContentLoaded', function() {
    const locationSelect = document.getElementById('location_id');
    const pollutantSelect = document.getElementById('pollutant');
    const thresholdInput = document.getElementById('unhealthy_level');
    const methodsCheckboxes = document.querySelectorAll('input[name="delivery_methods[]"]');
    
    function updatePreview() {
        const location = locationSelect.options[locationSelect.selectedIndex]?.text || 'selected location';
        const pollutant = pollutantSelect.value;
        const threshold = thresholdInput.value;
        
        const selectedMethods = Array.from(methodsCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.nextElementSibling.textContent)
            .join(', ');
        
        document.getElementById('preview-location').textContent = location;
        document.getElementById('preview-pollutant').textContent = pollutant;
        document.getElementById('preview-threshold').textContent = threshold;
        document.getElementById('preview-methods').textContent = selectedMethods || 'selected methods';
    }
    
    locationSelect.addEventListener('change', updatePreview);
    pollutantSelect.addEventListener('change', updatePreview);
    thresholdInput.addEventListener('input', updatePreview);
    methodsCheckboxes.forEach(cb => cb.addEventListener('change', updatePreview));
    
    // Initial update
    updatePreview();
});
</script>

<?php require_once 'includes/footer.php'; ?>