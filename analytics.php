<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Get analytics data
$stmt = $pdo->query("
    SELECT 
        COUNT(DISTINCT device_id) as total_devices,
        AVG(pm25) as avg_pm25,
        AVG(pm10) as avg_pm10,
        AVG(temperature) as avg_temp,
        AVG(humidity) as avg_humidity,
        MAX(timestamp) as last_update
    FROM air_quality_data
");
$analyticsData = $stmt->fetch();

// Get device-specific data
$stmt = $pdo->query("
    SELECT d.name, d.location, d.status, 
           AVG(a.pm25) as avg_pm25, 
           AVG(a.pm10) as avg_pm10,
           MAX(a.timestamp) as last_reading
    FROM devices d
    LEFT JOIN air_quality_data a ON d.id = a.device_id
    GROUP BY d.id
");
$deviceData = $stmt->fetchAll();

// Get historical data for charts
$stmt = $pdo->query("
    SELECT DATE(timestamp) as date, 
           AVG(pm25) as avg_pm25, 
           AVG(pm10) as avg_pm10
    FROM air_quality_data
    WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(timestamp)
    ORDER BY date
");
$historicalData = $stmt->fetchAll();
?>

<div class="analytics-header">
    <h1>Analytics Dashboard</h1>
    <p>Comprehensive insights and performance metrics</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-wind"></i>
        </div>
        <div class="stat-info">
            <h3>Average PM2.5</h3>
            <span class="stat-number"><?= round($analyticsData['avg_pm25'], 2) ?> μg/m³</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-cloud"></i>
        </div>
        <div class="stat-info">
            <h3>Average PM10</h3>
            <span class="stat-number"><?= round($analyticsData['avg_pm10'], 2) ?> μg/m³</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-thermometer-half"></i>
        </div>
        <div class="stat-info">
            <h3>Average Temperature</h3>
            <span class="stat-number"><?= round($analyticsData['avg_temp'], 2) ?>°C</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-tint"></i>
        </div>
        <div class="stat-info">
            <h3>Average Humidity</h3>
            <span class="stat-number"><?= round($analyticsData['avg_humidity'], 2) ?>%</span>
        </div>
    </div>
</div>

<div class="analytics-content">
    <div class="chart-container">
        <h2>Air Quality Trends (Last 7 Days)</h2>
        <canvas id="airQualityChart" width="400" height="200"></canvas>
    </div>
    
    <div class="device-performance">
        <h2>Device Performance</h2>
        <table>
            <thead>
                <tr>
                    <th>Device Name</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Avg PM2.5</th>
                    <th>Avg PM10</th>
                    <th>Last Reading</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deviceData as $device): ?>
                <tr>
                    <td><?= htmlspecialchars($device['name']) ?></td>
                    <td><?= htmlspecialchars($device['location']) ?></td>
                    <td>
                        <span class="status-badge <?= $device['status'] ?>">
                            <?= ucfirst($device['status']) ?>
                        </span>
                    </td>
                    <td><?= round($device['avg_pm25'], 2) ?> μg/m³</td>
                    <td><?= round($device['avg_pm10'], 2) ?> μg/m³</td>
                    <td><?= $device['last_reading'] ? date('M j, H:i', strtotime($device['last_reading'])) : 'N/A' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Prepare data for the chart
    const dates = <?= json_encode(array_column($historicalData, 'date')) ?>;
    const pm25Data = <?= json_encode(array_column($historicalData, 'avg_pm25')) ?>;
    const pm10Data = <?= json_encode(array_column($historicalData, 'avg_pm10')) ?>;

    // Render chart when page loads
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('airQualityChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'PM2.5 (μg/m³)',
                        data: pm25Data,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.1
                    },
                    {
                        label: 'PM10 (μg/m³)',
                        data: pm10Data,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>