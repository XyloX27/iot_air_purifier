<?php
// Check if admin user exists
$host = 'localhost';
$dbname = 'iot_air_purifier';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check users table
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Users in Database</h2>";
    echo "<pre>";
    print_r($users);
    echo "</pre>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>