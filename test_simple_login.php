<?php
session_start();
require_once 'config/database.php';

echo "<h1>🔧 Simple Login System Test</h1>";

// Test 1: Check if admin user exists with correct password
echo "<h2>Test 1: Admin User Check</h2>";

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin' AND password_hash = 'admin'");
$stmt->execute();
$admin = $stmt->fetch();

if ($admin) {
    echo "✅ Admin user found and password is correct<br>";
    echo "User ID: {$admin['id']}<br>";
    echo "Role: {$admin['role']}<br>";
} else {
    echo "❌ Admin user not found or password incorrect<br>";
    
    // Try to fix it
    $stmt = $pdo->prepare("UPDATE users SET password_hash = 'admin', role = 'admin' WHERE username = 'admin'");
    $stmt->execute();
    echo "🔧 Attempted to fix admin password<br>";
}

// Test 2: Test login process
echo "<h2>Test 2: Login Process Test</h2>";

$testUsername = 'admin';
$testPassword = 'admin';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password_hash = ?");
$stmt->execute([$testUsername, $testPassword]);
$user = $stmt->fetch();

if ($user) {
    echo "✅ Login would succeed!<br>";
    echo "User: {$user['username']}<br>";
    echo "Role: {$user['role']}<br>";
    
    // Simulate session creation
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    
    if ($user['role'] === 'admin') {
        $_SESSION['permissions'] = ['dashboard', 'sensors', 'locations', 'alerts', 'manual_data', 'reports', 'settings'];
        echo "✅ Admin permissions set<br>";
    }
    
    echo "✅ Session created successfully<br>";
} else {
    echo "❌ Login would fail!<br>";
}

// Test 3: Show current session
echo "<h2>Test 3: Current Session</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// Test 4: Show all users
echo "<h2>Test 4: All Users</h2>";

$stmt = $pdo->query("SELECT id, username, password_hash, role FROM users ORDER BY id");
$users = $stmt->fetchAll();

if ($users) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
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
} else {
    echo "No users found<br>";
}

echo "<hr>";
echo "<h2>🎯 Next Steps</h2>";
echo "<p>1. <a href='login.php'>Go to Login Page</a></p>";
echo "<p>2. Login with: <strong>admin</strong> / <strong>admin</strong></p>";
echo "<p>3. Go to Settings → Users tab to create new users</p>";

// Clear session for testing
session_destroy();
?>
