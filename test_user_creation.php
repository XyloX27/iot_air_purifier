<?php
session_start();
require_once 'config/database.php';

echo "<h1>🔧 User Creation and Login Test</h1>";

// Test 1: Create a test user
echo "<h2>Test 1: Creating Test User</h2>";

$testUsername = 'testuser';
$testPassword = 'test123';
$testEmail = 'test@example.com';

// First, delete if exists
$stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
$stmt->execute([$testUsername]);

// Create new user
try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, role, phone) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$testUsername, $testEmail, $testPassword, 'Test User', 'user', '+880 1234-567890']);
    
    $newUserId = $pdo->lastInsertId();
    echo "✅ Test user created successfully!<br>";
    echo "User ID: {$newUserId}<br>";
    echo "Username: {$testUsername}<br>";
    echo "Password: {$testPassword}<br>";
    
} catch (Exception $e) {
    echo "❌ Error creating test user: " . $e->getMessage() . "<br>";
}

// Test 2: Try to login with the new user
echo "<h2>Test 2: Login Test with New User</h2>";

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password_hash = ?");
$stmt->execute([$testUsername, $testPassword]);
$user = $stmt->fetch();

if ($user) {
    echo "✅ Login test successful!<br>";
    echo "User ID: {$user['id']}<br>";
    echo "Username: {$user['username']}<br>";
    echo "Role: {$user['role']}<br>";
    echo "Password matches: " . ($user['password_hash'] === $testPassword ? 'Yes' : 'No') . "<br>";
} else {
    echo "❌ Login test failed!<br>";
}

// Test 3: Show all users
echo "<h2>Test 3: All Users in Database</h2>";

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

// Test 4: Test login form
echo "<h2>Test 4: Manual Login Test</h2>";
echo '<form method="POST" style="border: 1px solid #ccc; padding: 20px; margin: 10px 0;">';
echo '<h3>Test Login Form</h3>';
echo '<p><strong>Test User:</strong> ' . $testUsername . '</p>';
echo '<p><strong>Test Password:</strong> ' . $testPassword . '</p>';
echo '<input type="text" name="test_username" placeholder="Username" value="' . $testUsername . '" style="margin: 5px;"><br>';
echo '<input type="password" name="test_password" placeholder="Password" value="' . $testPassword . '" style="margin: 5px;"><br>';
echo '<button type="submit" style="margin: 10px 5px;">Test Login</button>';
echo '</form>';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['test_username'];
    $password = $_POST['test_password'];
    
    echo "<h3>Login Test Results:</h3>";
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password_hash = ?");
    $stmt->execute([$username, $password]);
    $testUser = $stmt->fetch();
    
    if ($testUser) {
        echo "✅ Login successful!<br>";
        echo "User: {$testUser['username']}<br>";
        echo "Role: {$testUser['role']}<br>";
        
        // Simulate session creation
        $_SESSION['user_id'] = $testUser['id'];
        $_SESSION['username'] = $testUser['username'];
        $_SESSION['role'] = $testUser['role'];
        
        if ($testUser['role'] === 'admin') {
            $_SESSION['permissions'] = ['dashboard', 'sensors', 'locations', 'alerts', 'manual_data', 'reports', 'settings'];
        } else {
            $_SESSION['permissions'] = ['dashboard'];
        }
        
        echo "✅ Session created!<br>";
        echo "<a href='dashboard.php'>Go to Dashboard</a><br>";
    } else {
        echo "❌ Login failed!<br>";
        echo "Username: {$username}<br>";
        echo "Password: {$password}<br>";
    }
}

echo "<hr>";
echo "<h2>🎯 Instructions</h2>";
echo "<p>1. Go to <a href='settings.php'>Settings</a> → Users tab</p>";
echo "<p>2. Create a new user with a simple password</p>";
echo "<p>3. Try to login with that user at <a href='login.php'>Login Page</a></p>";
echo "<p>4. If it doesn't work, check the database for the user</p>";

// Clean up test user
$stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
$stmt->execute([$testUsername]);
echo "<p><em>Test user cleaned up.</em></p>";
?>
