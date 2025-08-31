<?php
require_once 'config/database.php';

echo "<h1>Simple Login System Setup</h1>";

try {
    // 1. Check if admin user exists
    echo "<h2>1. Checking admin user...</h2>";
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✅ Admin user found:<br>";
        echo "ID: {$admin['id']}<br>";
        echo "Username: {$admin['username']}<br>";
        echo "Password: {$admin['password_hash']}<br>";
        echo "Role: {$admin['role']}<br>";
        
        // Update admin password to simple "admin"
        $stmt = $pdo->prepare("UPDATE users SET password_hash = 'admin', role = 'admin' WHERE username = 'admin'");
        $stmt->execute();
        echo "✅ Admin password updated to 'admin'<br>";
        
    } else {
        echo "❌ Admin user not found. Creating one...<br>";
        
        // Create admin user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, role, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@airquality.com', 'admin', 'System Administrator', 'admin', '+880 1234-567890']);
        
        echo "✅ Admin user created successfully<br>";
    }
    
    // 2. Test login
    echo "<h2>2. Testing login...</h2>";
    
    $testUsername = 'admin';
    $testPassword = 'admin';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password_hash = ?");
    $stmt->execute([$testUsername, $testPassword]);
    $testUser = $stmt->fetch();
    
    if ($testUser) {
        echo "✅ Login test successful!<br>";
        echo "User ID: {$testUser['id']}<br>";
        echo "Role: {$testUser['role']}<br>";
    } else {
        echo "❌ Login test failed!<br>";
    }
    
    // 3. Show all users
    echo "<h2>3. Current users:</h2>";
    
    $stmt = $pdo->query("SELECT id, username, password_hash, role FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    
    if ($users) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Password</th><th>Role</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['password_hash']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h2>🎉 Setup Complete!</h2>";
    echo "<p><strong>Login Credentials:</strong></p>";
    echo "<p>Username: <strong>admin</strong></p>";
    echo "<p>Password: <strong>admin</strong></p>";
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
