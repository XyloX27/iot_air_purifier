<?php
session_start();
require_once 'config/database.php';

echo "<h2>Database Connection Test</h2>";

// Test database connection
try {
    echo "✅ Database connection successful<br>";
    
    // Check if users table exists and has data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✅ Users table exists with {$result['count']} users<br>";
    
    // Show existing users
    $stmt = $pdo->query("SELECT id, username, email, role FROM users LIMIT 5");
    $users = $stmt->fetchAll();
    echo "<h3>Existing Users:</h3>";
    echo "<ul>";
    foreach ($users as $user) {
        echo "<li>ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}</li>";
    }
    echo "</ul>";
    
    // Check if user_permissions table exists
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_permissions");
        $result = $stmt->fetch();
        echo "✅ User permissions table exists with {$result['count']} permissions<br>";
    } catch (Exception $e) {
        echo "❌ User permissions table does not exist: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>Session Test</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session data: <pre>" . print_r($_SESSION, true) . "</pre>";

echo "<hr>";
echo "<h2>Test Login</h2>";
echo '<form method="POST">';
echo '<input type="text" name="test_username" placeholder="Username" value="admin"><br>';
echo '<input type="password" name="test_password" placeholder="Password" value="admin"><br>';
echo '<button type="submit">Test Login</button>';
echo '</form>';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['test_username'];
    $password = $_POST['test_password'];
    
    echo "<h3>Login Test Results:</h3>";
    
    // Test user lookup
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ User found: {$user['username']}<br>";
        echo "User ID: {$user['id']}<br>";
        echo "User Role: {$user['role']}<br>";
        echo "Password Hash: {$user['password_hash']}<br>";
        echo "Input Password: {$password}<br>";
        
        if ($password === $user['password_hash']) {
            echo "✅ Password match successful!<br>";
            
            // Test permission loading
            try {
                $stmt = $pdo->prepare("SELECT permission_name FROM user_permissions WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if ($permissions) {
                    echo "✅ Permissions loaded: " . implode(', ', $permissions) . "<br>";
                } else {
                    echo "⚠️ No permissions found for user<br>";
                }
            } catch (Exception $e) {
                echo "❌ Error loading permissions: " . $e->getMessage() . "<br>";
            }
            
        } else {
            echo "❌ Password mismatch!<br>";
        }
    } else {
        echo "❌ User not found<br>";
    }
}
?>
