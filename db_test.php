<?php
// Database connection test file
// This file helps verify that your database connection is working properly

echo "<h1>Database Connection Test</h1>";
echo "<p>Testing connection to MySQL database...</p>";

// Database configuration
$host = 'localhost';
$dbname = 'iot_air_purifier';
$username = 'root';
$password = '';

try {
    // Test connection without database first
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ Successfully connected to MySQL server</p>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Database '$dbname' exists</p>";
        
        // Connect to the specific database
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<p style='color: green;'>✓ Successfully connected to database '$dbname'</p>";
        
        // Check if tables exist
        $tables = ['users', 'devices', 'sensors', 'locations', 'alerts', 'manual_data', 'reports'];
        echo "<h3>Table Status:</h3>";
        
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
                $count = $stmt->fetchColumn();
                echo "<p style='color: green;'>✓ Table '$table' exists with $count records</p>";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>✗ Table '$table' does not exist or has errors</p>";
            }
        }
        
        // Show sample data
        echo "<h3>Sample Data:</h3>";
        
        // Check users
        try {
            $stmt = $pdo->query("SELECT username, email, role FROM users LIMIT 3");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($users) {
                echo "<p><strong>Users:</strong></p>";
                foreach ($users as $user) {
                    echo "<p>- {$user['username']} ({$user['email']}) - {$user['role']}</p>";
                }
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error reading users: " . $e->getMessage() . "</p>";
        }
        
        // Check devices
        try {
            $stmt = $pdo->query("SELECT device_id, name, type, status FROM devices LIMIT 3");
            $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($devices) {
                echo "<p><strong>Devices:</strong></p>";
                foreach ($devices as $device) {
                    echo "<p>- {$device['device_id']}: {$device['name']} ({$device['type']}) - {$device['status']}</p>";
                }
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error reading devices: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database '$dbname' does not exist</p>";
        echo "<p>Please run the database setup script first.</p>";
        echo "<p><a href='database_setup.sql' target='_blank'>View Database Setup Script</a></p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>✗ Connection failed: " . $e->getMessage() . "</p>";
    echo "<h3>Troubleshooting Tips:</h3>";
    echo "<ul>";
    echo "<li>Make sure XAMPP is running and MySQL service is started</li>";
    echo "<li>Check if the username and password are correct</li>";
    echo "<li>Verify that MySQL is running on port 3306</li>";
    echo "<li>Make sure the database 'iot_air_purifier' exists</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If connection failed, start XAMPP and MySQL service</li>";
echo "<li>If database doesn't exist, run the database setup script in phpMyAdmin</li>";
echo "<li>Once everything is working, you can access the main application</li>";
echo "</ol>";

echo "<p><a href='index.php'>← Back to Application</a></p>";
?>