<?php
// Reset admin password
$host = 'localhost';
$dbname = 'iot_air_purifier';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Reset admin password to 'admin123'
    $newPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$newPassword]);
    
    echo "Admin password reset to 'admin123' successfully!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>