<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total_devices FROM devices");
$totalDevices = $stmt->fetch()['total_devices'];

$stmt = $pdo->query("SELECT COUNT(*) as online_devices FROM devices WHERE status = 'online'");
$onlineDevices = $stmt->fetch()['online_devices'];

$stmt = $pdo->query("SELECT COUNT(*) as total_locations FROM locations");
$totalLocations = $stmt->fetch()['total_locations'];

// Get latest air quality data
$stmt = $pdo->query("
    SELECT a.*, d.name as device_name, l.name as location_name
    FROM air_quality_data a 
    JOIN devices d ON a.device_id = d.id 
    JOIN locations l ON d.location_id = l.id
    ORDER BY a.timestamp DESC 
    LIMIT 5
");
$latestReadings = $stmt->fetchAll();

// Get data for histogram (last 15 days of PM2.5 readings)
$stmt = $pdo->query("
    SELECT DATE(timestamp) as date, AVG(pm25) as avg_pm25, COUNT(*) as reading_count
    FROM air_quality_data 
    WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 15 DAY)
    GROUP BY DATE(timestamp)
    ORDER BY date
");
$histogramData = $stmt->fetchAll();

// Get data for pie chart (device status distribution)
$stmt = $pdo->query("
    SELECT status, COUNT(*) as count
    FROM devices 
    GROUP BY status
");
$deviceStatusData = $stmt->fetchAll();

// If no data, create some dummy data for demonstration
if (empty($latestReadings)) {
    $latestReadings = [
        ['device_name' => 'Dhaka Sensor 1', 'location_name' => 'Dhaka Division', 'pm25' => 35.2, 'pm10' => 68.7, 'co2' => 420.5, 'co' => 0.8, 'temperature' => 28.5, 'humidity' => 65.0, 'timestamp' => date('Y-m-d H:i:s')],
        ['device_name' => 'Chittagong Sensor 1', 'location_name' => 'Chittagong Division', 'pm25' => 65.8, 'pm10' => 112.4, 'co2' => 480.7, 'co' => 2.5, 'temperature' => 30.2, 'humidity' => 58.7, 'timestamp' => date('Y-m-d H:i:s')],
        ['device_name' => 'Sylhet Sensor 1', 'location_name' => 'Sylhet Division', 'pm25' => 22.4, 'pm10' => 45.6, 'co2' => 405.1, 'co' => 0.3, 'temperature' => 26.5, 'humidity' => 72.3, 'timestamp' => date('Y-m-d H:i:s')]
    ];
}

// Create dummy chart data if no real data exists
if (empty($histogramData)) {
    $histogramData = [
        ['date' => date('Y-m-d', strtotime('-14 days')), 'avg_pm25' => 52.3, 'reading_count' => 24],
        ['date' => date('Y-m-d', strtotime('-13 days')), 'avg_pm25' => 48.7, 'reading_count' => 24],
        ['date' => date('Y-m-d', strtotime('-12 days')), 'avg_pm25' => 61.2, 'reading_count' => 24],
        ['date' => date('Y-m-d', strtotime('-11 days')), 'avg_pm25' => 55.8, 'reading_count' => 24],
        ['date' => date('Y-m-d', strtotime('-10 days')), 'avg_pm25' => 43.1, 'reading_count' => 24],
        ['date' => date('Y-m-d', strtotime('-9 days')), 'avg_pm25' => 67.4, 'reading_count' => 24],
        ['date' => date('Y-m-d', strtotime('-8 days')), 'avg_pm25' => 58.9, 'reading_count' => 24],
        ['date' => date('Y-m-d', strtotime('-7 days')), 'avg_pm25' => 49.6, 'reading_count' => 24],
        ['date' => date('Y-m-d', strtotime('-6 days')), 'avg_pm25' => 72.1, 'reading_count' => 24],
        ['date' => date('Y-m-d', strtotime('-5 days')), 'avg_pm25' => 63.8, 'reading_count' => 24],
        ['date' => date('Y-m-d', strtotime('-4 days')), 'avg_pm25' => 81.3, 'reading_count' => 24],
        ['date' => date('Y-m-d', strtotime('-3 days')), 'avg_pm25' => 76.5, 'reading_count' => 24],
        ['date' => date('Y-m-d', strtotime('-2 days')), 'avg_pm25' => 68.9, 'reading_count' => 24],
        ['date' => date('Y-m-d', strtotime('-1 days')), 'avg_pm25' => 59.2, 'reading_count' => 24],
        ['date' => date('Y-m-d'), 'avg_pm25' => 65.8, 'reading_count' => 24]
    ];
}

if (empty($deviceStatusData)) {
    $deviceStatusData = [
        ['status' => 'online', 'count' => 3],
        ['status' => 'offline', 'count' => 2],
        ['status' => 'maintenance', 'count' => 1]
    ];
}
?>

<div class="dashboard-header">
    <h1>Dashboard</h1>
    <p>Welcome back, <?= $_SESSION['username'] ?>! Here's your system overview.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-wind"></i>
        </div>
        <div class="stat-info">
            <h3>Total Devices</h3>
            <span class="stat-number"><?= $totalDevices ?></span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon online">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3>Online Devices</h3>
            <span class="stat-number"><?= $onlineDevices ?></span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-map-marker-alt"></i>
        </div>
        <div class="stat-info">
            <h3>Monitoring Locations</h3>
            <span class="stat-number"><?= $totalLocations ?></span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-info">
            <h3>Air Quality Index</h3>
            <span class="stat-number"><?= $latestReadings[0]['pm25'] ?? 'N/A' ?> μg/m³</span>
        </div>
    </div>
</div>

<div class="charts-section">
    <div class="chart-container">
        <h2>Air Quality Trends (Last 15 Days)</h2>
        <div class="chart-wrapper">
            <canvas id="airQualityHistogram"></canvas>
        </div>
    </div>
    
    <div class="chart-container">
        <h2>Device Status Distribution</h2>
        <div class="chart-wrapper">
            <canvas id="deviceStatusPie"></canvas>
        </div>
    </div>
</div>

<div class="dashboard-content">
    <div class="recent-readings">
        <h2>Recent Air Quality Readings</h2>
        <table>
            <thead>
                <tr>
                    <th>Device</th>
                    <th>Location</th>
                    <th>PM2.5</th>
                    <th>PM10</th>
                    <th>CO₂</th>
                    <th>CO</th>
                    <th>Temp</th>
                    <th>Humidity</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($latestReadings as $reading): ?>
                <tr>
                    <td><?= htmlspecialchars($reading['device_name']) ?></td>
                    <td><?= htmlspecialchars($reading['location_name']) ?></td>
                    <td><?= $reading['pm25'] ?> μg/m³</td>
                    <td><?= $reading['pm10'] ?> μg/m³</td>
                    <td><?= $reading['co2'] ?> ppm</td>
                    <td><?= $reading['co'] ?> ppm</td>
                    <td><?= $reading['temperature'] ?>°C</td>
                    <td><?= $reading['humidity'] ?>%</td>
                    <td><?= date('M j, H:i', strtotime($reading['timestamp'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="action-buttons">
            <a href="sensors.php" class="action-btn">
                <i class="fas fa-plus"></i>
                <span>Add New Sensor</span>
            </a>
            <a href="manual_data.php" class="action-btn">
                <i class="fas fa-pencil-alt"></i>
                <span>Manual Data Entry</span>
            </a>
            <a href="alerts.php" class="action-btn">
                <i class="fas fa-bell"></i>
                <span>Configure Alerts</span>
            </a>
            <a href="reports.php" class="action-btn">
                <i class="fas fa-file-export"></i>
                <span>Generate Reports</span>
            </a>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing charts...');
    
    // Prepare chart data
    const histogramData = <?= json_encode($histogramData) ?>;
    const deviceStatusData = <?= json_encode($deviceStatusData) ?>;
    
    console.log('Histogram data:', histogramData);
    console.log('Device status data:', deviceStatusData);

    // Air Quality Histogram
    const histogramCtx = document.getElementById('airQualityHistogram');
    console.log('Histogram canvas element:', histogramCtx);
    
    if (histogramCtx) {
        try {
            const airQualityHistogram = new Chart(histogramCtx, {
                type: 'bar',
                data: {
                    labels: histogramData.map(item => {
                        const date = new Date(item.date);
                        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    }),
                    datasets: [
                        {
                            label: 'Average PM2.5 (μg/m³)',
                            data: histogramData.map(item => item.avg_pm25),
                            backgroundColor: 'rgba(102, 126, 234, 0.8)',
                            borderColor: 'rgba(102, 126, 234, 1)',
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                            order: 2
                        },
                        {
                            label: 'Moving Average',
                            data: histogramData.map(item => item.avg_pm25),
                            type: 'line',
                            borderColor: 'rgba(231, 76, 60, 1)',
                            borderWidth: 3,
                            backgroundColor: 'rgba(231, 76, 60, 0.1)',
                            fill: false,
                            tension: 0.4,
                            pointRadius: 6,
                            pointBackgroundColor: 'rgba(231, 76, 60, 1)',
                            pointBorderColor: 'white',
                            pointBorderWidth: 2,
                            pointHoverRadius: 8,
                            pointHoverBackgroundColor: 'rgba(231, 76, 60, 1)',
                            pointHoverBorderColor: 'white',
                            pointHoverBorderWidth: 3,
                            order: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeInOutQuart'
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                color: '#2c3e50',
                                font: {
                                    size: 14,
                                    weight: '600'
                                },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'PM2.5 (μg/m³)',
                                color: '#2c3e50',
                                font: {
                                    size: 14,
                                    weight: '600'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                color: '#2c3e50'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date',
                                color: '#2c3e50',
                                font: {
                                    size: 14,
                                    weight: '600'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                color: '#2c3e50'
                            }
                        }
                    }
                }
            });
            console.log('Histogram chart created successfully');
        } catch (error) {
            console.error('Error creating histogram chart:', error);
        }
    } else {
        console.error('Histogram canvas element not found!');
    }

    // Device Status Pie Chart
    const pieCtx = document.getElementById('deviceStatusPie');
    console.log('Pie chart canvas element:', pieCtx);
    
    if (pieCtx) {
        try {
            const deviceStatusPie = new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: deviceStatusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
                    datasets: [{
                        data: deviceStatusData.map(item => item.count),
                        backgroundColor: [
                            'rgba(39, 174, 96, 0.8)',   // Green for online
                            'rgba(231, 76, 60, 0.8)',   // Red for offline
                            'rgba(243, 156, 18, 0.8)'   // Orange for maintenance
                        ],
                        borderColor: [
                            'rgba(39, 174, 96, 1)',
                            'rgba(231, 76, 60, 1)',
                            'rgba(243, 156, 18, 1)'
                        ],
                        borderWidth: 3,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 2000,
                        easing: 'easeInOutQuart'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: '#2c3e50',
                                font: {
                                    size: 14,
                                    weight: '600'
                                },
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
            console.log('Pie chart created successfully');
        } catch (error) {
            console.error('Error creating pie chart:', error);
        }
    } else {
        console.error('Pie chart canvas element not found!');
    }
    
    console.log('Chart initialization complete');
});
</script>

<?php require_once 'includes/footer.php'; ?>