<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['generate_report'])) {
        $type = $_POST['type'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $selected_locations = isset($_POST['locations']) ? json_encode($_POST['locations']) : '[]';
        $selected_pollutants = isset($_POST['pollutants']) ? json_encode($_POST['pollutants']) : '[]';
        $generated_by = $_SESSION['user_id'];
        
        // In a real application, you would generate the report file here
        // For this example, we'll just store the report parameters
        $stmt = $pdo->prepare("INSERT INTO reports (type, start_date, end_date, locations, pollutants, generated_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$type, $start_date, $end_date, $selected_locations, $selected_pollutants, $generated_by]);
        
        $report_id = $pdo->lastInsertId();
        
        // Redirect to a download page or show success message
        header('Location: reports.php?success=1&report_id=' . $report_id);
        exit;
    }
    
    if (isset($_POST['delete_report'])) {
        $report_id = $_POST['report_id'];
        
        // Delete the report
        $stmt = $pdo->prepare("DELETE FROM reports WHERE id = ?");
        $stmt->execute([$report_id]);
        
        header('Location: reports.php?deleted=1');
        exit;
    }
}

// Get all locations for checkboxes
$stmt = $pdo->query("SELECT id, name FROM locations ORDER BY name");
$locations = $stmt->fetchAll();

// Get all reports
$stmt = $pdo->query("
    SELECT r.*, u.username as generated_by_name 
    FROM reports r 
    JOIN users u ON r.generated_by = u.id 
    ORDER BY r.created_at DESC
");
$reports = $stmt->fetchAll();

// Show success message if report was generated
$success = isset($_GET['success']) ? $_GET['success'] : 0;
$report_id = isset($_GET['report_id']) ? $_GET['report_id'] : 0;
$deleted = isset($_GET['deleted']) ? $_GET['deleted'] : 0;
?>

<div class="page-header">
    <h1>Report Generation & Export</h1>
    <p>Generate and export air quality reports</p>
</div>

<?php if ($success): ?>
<div class="alert alert-success">
    Report #<?= $report_id ?> has been generated successfully. 
    <a href="download_report.php?id=<?= $report_id ?>&format=pdf" class="btn btn-primary btn-sm">
        <i class="fas fa-file-pdf"></i> Download PDF
    </a>
    <a href="download_report.php?id=<?= $report_id ?>&format=excel" class="btn btn-success btn-sm">
        <i class="fas fa-file-excel"></i> Download Excel
    </a>
</div>
<?php endif; ?>

<?php if ($deleted): ?>
<div class="alert alert-success">
    Report has been deleted successfully.
</div>
<?php endif; ?>

<div class="page-content">
    <div class="form-section">
        <h2>Generate New Report</h2>
        <form method="POST" class="card">
            <div class="form-group">
                <label for="type">Report Type</label>
                <select id="type" name="type" required>
                    <option value="compliance">Compliance Report</option>
                    <option value="health">Health Impact Report</option>
                    <option value="technical">Technical Analysis</option>
                    <option value="custom" selected>Custom Report</option>
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?= date('Y-m-d', strtotime('-7 days')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Select Locations</label>
                <div class="checkbox-group">
                    <label><input type="checkbox" id="select-all-locations"> Select All Locations</label>
                    <div class="checkbox-columns">
                        <?php foreach ($locations as $location): ?>
                        <label><input type="checkbox" name="locations[]" value="<?= $location['id'] ?>"> <?= htmlspecialchars($location['name']) ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <small>Hold Ctrl/Cmd to select multiple locations</small>
            </div>
            
            <div class="form-group">
                <label>Pollutants to Include</label>
                <div class="checkbox-group">
                    <label><input type="checkbox" id="select-all-pollutants"> Select All Pollutants</label>
                    <div class="checkbox-list">
                        <label><input type="checkbox" name="pollutants[]" value="PM2.5" checked> PM2.5</label>
                        <label><input type="checkbox" name="pollutants[]" value="PM10" checked> PM10</label>
                        <label><input type="checkbox" name="pollutants[]" value="CO2"> CO₂</label>
                        <label><input type="checkbox" name="pollutants[]" value="CO"> CO</label>
                        <label><input type="checkbox" name="pollutants[]" value="NO2"> NO₂</label>
                        <label><input type="checkbox" name="pollutants[]" value="SO2"> SO₂</label>
                        <label><input type="checkbox" name="pollutants[]" value="AQI"> AQI</label>
                    </div>
                </div>
            </div>
            
            <button type="submit" name="generate_report" class="btn btn-primary">Generate Report</button>
        </form>
    </div>
    
    <div class="list-section">
        <h2>Generated Reports</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Report ID</th>
                        <th>Type</th>
                        <th>Date Range</th>
                        <th>Generated By</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): 
                        $locations = json_decode($report['locations'], true);
                        $location_count = is_array($locations) ? count($locations) : 0;
                    ?>
                    <tr>
                        <td>#<?= $report['id'] ?></td>
                        <td><?= ucfirst($report['type']) ?></td>
                        <td><?= date('M j, Y', strtotime($report['start_date'])) ?> - <?= date('M j, Y', strtotime($report['end_date'])) ?></td>
                        <td><?= htmlspecialchars($report['generated_by_name']) ?></td>
                        <td><?= date('M j, Y', strtotime($report['created_at'])) ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="download_report.php?id=<?= $report['id'] ?>&format=pdf" class="btn btn-primary btn-sm" title="Download PDF">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                                <a href="download_report.php?id=<?= $report['id'] ?>&format=excel" class="btn btn-success btn-sm" title="Download Excel">
                                    <i class="fas fa-file-excel"></i> Excel
                                </a>
                                <form method="POST" class="inline-form" style="display: inline;">
                                    <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                    <button type="submit" name="delete_report" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this report?')" title="Delete Report">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all locations checkbox
    document.getElementById('select-all-locations').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="locations[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
    
    // Select all pollutants checkbox
    document.getElementById('select-all-pollutants').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="pollutants[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>