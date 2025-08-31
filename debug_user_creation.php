<?php
session_start();
require_once 'config/database.php';

echo "<h1>🔍 User Creation Debug</h1>";

// Test 1: Check database connection
echo "<h2>Test 1: Database Connection</h2>";
try {
    $pdo->query("SELECT 1");
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Check users table structure
echo "<h2>Test 2: Users Table Structure</h2>";
try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Users table exists with columns:<br>";
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})<br>";
    }
} catch (Exception $e) {
    echo "❌ Users table error: " . $e->getMessage() . "<br>";
}

// Test 3: Show current users
echo "<h2>Test 3: Current Users</h2>";
try {
    $stmt = $pdo->query("SELECT id, username, password_hash, role, email FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($users) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Password</th><th>Role</th><th>Email</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['password_hash']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "No users found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Error fetching users: " . $e->getMessage() . "<br>";
}

// Test 4: Create a test user manually
echo "<h2>Test 4: Manual User Creation</h2>";

$testUsername = 'debuguser';
$testPassword = 'debug123';
$testEmail = 'debug@test.com';

// Delete if exists
try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
    $stmt->execute([$testUsername]);
    echo "✅ Cleaned up existing test user<br>";
} catch (Exception $e) {
    echo "⚠️ Cleanup warning: " . $e->getMessage() . "<br>";
}

// Create user step by step
try {
    echo "Creating user with:<br>";
    echo "- Username: $testUsername<br>";
    echo "- Password: $testPassword<br>";
    echo "- Email: $testEmail<br>";
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, role, phone) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$testUsername, $testEmail, $testPassword, 'Debug Test User', 'user', '+880 1234-567890']);
    
    if ($result) {
        $newUserId = $pdo->lastInsertId();
        echo "✅ User created successfully! ID: $newUserId<br>";
        
        // Verify the user was created
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$newUserId]);
        $createdUser = $stmt->fetch();
        
        if ($createdUser) {
            echo "✅ User verification successful:<br>";
            echo "- ID: {$createdUser['id']}<br>";
            echo "- Username: {$createdUser['username']}<br>";
            echo "- Password: {$createdUser['password_hash']}<br>";
            echo "- Role: {$createdUser['role']}<br>";
        } else {
            echo "❌ User verification failed<br>";
        }
    } else {
        echo "❌ User creation failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Error creating user: " . $e->getMessage() . "<br>";
}

// Test 5: Test login with the created user
echo "<h2>Test 5: Login Test</h2>";

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password_hash = ?");
    $stmt->execute([$testUsername, $testPassword]);
    $loginUser = $stmt->fetch();
    
    if ($loginUser) {
        echo "✅ Login test successful!<br>";
        echo "- User ID: {$loginUser['id']}<br>";
        echo "- Username: {$loginUser['username']}<br>";
        echo "- Role: {$loginUser['role']}<br>";
        
        // Simulate session creation
        $_SESSION['user_id'] = $loginUser['id'];
        $_SESSION['username'] = $loginUser['username'];
        $_SESSION['role'] = $loginUser['role'];
        $_SESSION['permissions'] = ['dashboard'];
        
        echo "✅ Session created successfully<br>";
        echo "<a href='dashboard.php'>Go to Dashboard</a><br>";
    } else {
        echo "❌ Login test failed<br>";
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$testUsername]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            echo "⚠️ User exists but password doesn't match<br>";
            echo "Expected password: {$exists['password_hash']}<br>";
            echo "Provided password: $testPassword<br>";
        } else {
            echo "⚠️ User doesn't exist<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Login test error: " . $e->getMessage() . "<br>";
}

// Test 6: Manual login form
echo "<h2>Test 6: Manual Login Form</h2>";
echo '<form method="POST" style="border: 1px solid #ccc; padding: 20px; margin: 10px 0;">';
echo '<h3>Test Login</h3>';
echo '<p><strong>Test User:</strong> ' . $testUsername . '</p>';
echo '<p><strong>Test Password:</strong> ' . $testPassword . '</p>';
echo '<input type="text" name="test_username" placeholder="Username" value="' . $testUsername . '" style="margin: 5px;"><br>';
echo '<input type="password" name="test_password" placeholder="Password" value="' . $testPassword . '" style="margin: 5px;"><br>';
echo '<button type="submit" style="margin: 10px 5px;">Test Login</button>';
echo '</form>';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['test_username'];
    $password = $_POST['test_password'];
    
    echo "<h3>Manual Login Results:</h3>";
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password_hash = ?");
    $stmt->execute([$username, $password]);
    $manualUser = $stmt->fetch();
    
    if ($manualUser) {
        echo "✅ Manual login successful!<br>";
        echo "User: {$manualUser['username']}<br>";
        echo "Role: {$manualUser['role']}<br>";
        
        $_SESSION['user_id'] = $manualUser['id'];
        $_SESSION['username'] = $manualUser['username'];
        $_SESSION['role'] = $manualUser['role'];
        $_SESSION['permissions'] = ['dashboard'];
        
        echo "✅ Session created!<br>";
        echo "<a href='dashboard.php'>Go to Dashboard</a><br>";
    } else {
        echo "❌ Manual login failed<br>";
    }
}

echo "<hr>";
echo "<h2>🎯 Next Steps</h2>";
echo "<p>1. If the manual user creation works, the issue is in the Settings form</p>";
echo "<p>2. If manual creation fails, there's a database issue</p>";
echo "<p>3. Try creating a user in <a href='settings.php'>Settings</a> → Users tab</p>";
echo "<p>4. Check the PHP error logs for any errors</p>";

// Clean up
try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
    $stmt->execute([$testUsername]);
    echo "<p><em>Test user cleaned up.</em></p>";
} catch (Exception $e) {
    echo "<p><em>Cleanup failed: " . $e->getMessage() . "</em></p>";
}
?>
