<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_location'])) {
        $name = $_POST['name'];
        $type = $_POST['type'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        $priority = $_POST['priority'];
        $radius = $_POST['radius'];
        $description = $_POST['description'];
        $considerations = isset($_POST['considerations']) ? json_encode($_POST['considerations']) : '[]';
        
        $stmt = $pdo->prepare("INSERT INTO locations (name, type, latitude, longitude, priority, radius, description, considerations) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $type, $latitude, $longitude, $priority, $radius, $description, $considerations]);
        
        header('Location: locations.php');
        exit;
    }
    
    if (isset($_POST['delete_location'])) {
        $locationId = $_POST['location_id'];
        
        $stmt = $pdo->prepare("DELETE FROM locations WHERE id = ?");
        $stmt->execute([$locationId]);
        
        header('Location: locations.php');
        exit;
    }
}

// Get all locations
$stmt = $pdo->query("SELECT * FROM locations ORDER BY created_at DESC");
$locations = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>Location & Area Management</h1>
    <p>Manage monitoring locations and areas</p>
</div>

<div class="page-content">
    <div class="form-section">
        <h2>Add New Location</h2>
        <form method="POST" class="card">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Area/Zone Name</label>
                    <input type="text" id="name" name="name" required placeholder="e.g., Industrial Zone - Savar">
                </div>
                <div class="form-group">
                    <label for="type">Area Type</label>
                    <select id="type" name="type" required>
                        <option value="industrial">Industrial</option>
                        <option value="residential">Residential</option>
                        <option value="commercial">Commercial</option>
                        <option value="rural">Rural</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="latitude">Latitude</label>
                    <input type="number" id="latitude" name="latitude" step="any" required placeholder="e.g., 23.8103">
                </div>
                <div class="form-group">
                    <label for="longitude">Longitude</label>
                    <input type="number" id="longitude" name="longitude" step="any" required placeholder="e.g., 90.4125">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="priority">Monitoring Priority</label>
                    <select id="priority" name="priority" required>
                        <option value="low">Low Priority</option>
                        <option value="medium" selected>Medium Priority</option>
                        <option value="high">High Priority</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="radius">Coverage Radius (km)</label>
                    <input type="number" id="radius" name="radius" step="0.1" value="2.0" min="0.1" max="100">
                </div>
            </div>
            
            <div class="form-group">
                <label>Special Considerations</label>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="considerations[]" value="seasonal"> Seasonal Variation Expected</label>
                    <label><input type="checkbox" name="considerations[]" value="traffic"> Traffic Influence</label>
                    <label><input type="checkbox" name="considerations[]" value="industrial"> Near Industrial Source</label>
                    <label><input type="checkbox" name="considerations[]" value="construction"> Construction Activity</label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Area Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Describe the area characteristics, nearby landmarks, pollution sources, etc."></textarea>
            </div>
            
            <button type="submit" name="add_location" class="btn btn-primary">Add Location</button>
        </form>
    </div>
    
    <div class="list-section">
        <h2>Your Locations</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Coordinates</th>
                        <th>Priority</th>
                        <th>Radius (km)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locations as $location): ?>
                    <tr>
                        <td><?= htmlspecialchars($location['name']) ?></td>
                        <td><?= ucfirst($location['type']) ?></td>
                        <td><?= $location['latitude'] ?>, <?= $location['longitude'] ?></td>
                        <td>
                            <span class="priority-badge <?= $location['priority'] ?>">
                                <?= ucfirst($location['priority']) ?>
                            </span>
                        </td>
                        <td><?= $location['radius'] ?></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="location_id" value="<?= $location['id'] ?>">
                                <button type="submit" name="delete_location" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this location?')">
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