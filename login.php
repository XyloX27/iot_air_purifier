<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Debug: Log the login attempt
    error_log("Login attempt - Username: $username, Password: $password");
    
    // Simple authentication - no hashing
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ? AND password_hash = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();
    
    // Debug: Log the result
    if ($user) {
        error_log("Login successful for user: " . $user['username']);
    } else {
        error_log("Login failed for username: $username");
        
        // Check if user exists but password is wrong
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $existingUser = $stmt->fetch();
        if ($existingUser) {
            error_log("User exists but password mismatch. Expected: " . $existingUser['password_hash'] . ", Got: $password");
        } else {
            error_log("User does not exist");
        }
    }
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Set permissions based on role
        if ($user['role'] === 'admin') {
            $_SESSION['permissions'] = ['dashboard', 'sensors', 'locations', 'alerts', 'manual_data', 'reports', 'settings'];
        } else {
            // For non-admin users, try to get permissions from database
            try {
                $stmt = $pdo->prepare("SELECT permission_name FROM user_permissions WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $_SESSION['permissions'] = $permissions ?: ['dashboard'];
            } catch (Exception $e) {
                $_SESSION['permissions'] = ['dashboard'];
            }
        }
        
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AirQuality</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <div class="brand">
                <h1>AirQuality</h1>
                <p>IoT Air Quality Monitoring System</p>
            </div>
            <h2>Login to your account</h2>
            <?php if (isset($error)): ?>
                <div class="error-message"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required value="admin">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required value="admin">
                </div>
                <button type="submit">Login</button>
            </form>
            <p class="register-link">Don't have an account? <a href="#">Contact administrator</a></p>
        </div>
    </div>
</body>
</html>