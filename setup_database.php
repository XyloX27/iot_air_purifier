<?php
require_once 'config/database.php';

echo "<h1>Database Setup for User Management System</h1>";

try {
    // 1. Create user_permissions table
    echo "<h2>1. Creating user_permissions table...</h2>";
    
    $sql = "CREATE TABLE IF NOT EXISTS `user_permissions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `permission_name` varchar(50) NOT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_user_permission` (`user_id`, `permission_name`),
        KEY `idx_user_id` (`user_id`),
        KEY `idx_permission_name` (`permission_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ user_permissions table created successfully<br>";
    
    // 2. Check if admin user exists and has role
    echo "<h2>2. Checking admin user...</h2>";
    
    $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✅ Admin user found (ID: {$admin['id']})<br>";
        
        // Update admin role if not set
        if (empty($admin['role'])) {
            $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
            $stmt->execute([$admin['id']]);
            echo "✅ Admin role updated<br>";
        } else {
            echo "✅ Admin role already set: {$admin['role']}<br>";
        }
        
        // 3. Set admin permissions
        echo "<h2>3. Setting admin permissions...</h2>";
        
        $adminPermissions = ['dashboard', 'sensors', 'locations', 'alerts', 'manual_data', 'reports', 'settings'];
        
        foreach ($adminPermissions as $permission) {
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO user_permissions (user_id, permission_name) VALUES (?, ?)");
                $stmt->execute([$admin['id'], $permission]);
            } catch (Exception $e) {
                // Permission might already exist
            }
        }
        
        echo "✅ Admin permissions set<br>";
        
        // 4. Show current admin permissions
        $stmt = $pdo->prepare("SELECT permission_name FROM user_permissions WHERE user_id = ?");
        $stmt->execute([$admin['id']]);
        $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Current admin permissions: " . implode(', ', $permissions) . "<br>";
        
    } else {
        echo "❌ Admin user not found. Please ensure you have run the complete_database_setup.sql first.<br>";
    }
    
    // 5. Test login credentials
    echo "<h2>4. Testing login credentials...</h2>";
    
    $stmt = $pdo->prepare("SELECT username, password_hash, role FROM users WHERE username = 'admin'");
    $stmt->execute();
    $testUser = $stmt->fetch();
    
    if ($testUser) {
        echo "✅ Admin user credentials:<br>";
        echo "Username: {$testUser['username']}<br>";
        echo "Password Hash: {$testUser['password_hash']}<br>";
        echo "Role: {$testUser['role']}<br>";
        echo "<br>";
        echo "🔑 <strong>Login with:</strong><br>";
        echo "Username: admin<br>";
        echo "Password: admin<br>";
    }
    
    // 6. Show all users
    echo "<h2>5. Current users in system:</h2>";
    
    $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    
    if ($users) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Created</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>{$user['created_at']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "No users found in the system.<br>";
    }
    
    echo "<hr>";
    echo "<h2>🎉 Setup Complete!</h2>";
    echo "<p>Your user management system is now ready. You can:</p>";
    echo "<ul>";
    echo "<li><a href='login.php'>Go to Login Page</a></li>";
    echo "<li><a href='dashboard.php'>Go to Dashboard</a> (after login)</li>";
    echo "<li><a href='settings.php'>Go to Settings</a> (after login, admin only)</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ Error during setup: " . $e->getMessage() . "<br>";
    echo "Please check your database connection and try again.<br>";
}
?>
