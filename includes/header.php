<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header('Location: login.php');
    exit;
}

// Simple permission check - admin can access everything
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
if (isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    if ($_SESSION['role'] !== 'admin') {
        // For non-admin users, check if they have permission for current page
        if (!isset($_SESSION['permissions']) || !in_array($currentPage, $_SESSION['permissions'])) {
            $_SESSION['error'] = "You don't have permission to access this page.";
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AirQuality - IoT Air Quality Monitoring System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navigation-fix.css">
    <?php
    // Load page-specific CSS
    $page = basename($_SERVER['PHP_SELF']);
    $cssFile = 'css/' . pathinfo($page, PATHINFO_FILENAME) . '.css';
    if (file_exists($cssFile)) {
        echo '<link rel="stylesheet" href="' . $cssFile . '">';
    }
    ?>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-brand">
                <h2>AirQuality</h2>
                <span>IoT Air Quality Monitoring System</span>
            </div>
            <ul class="nav-menu">
                <?php
                // Define all menu items
                $allMenuItems = [
                    'dashboard' => 'Dashboard',
                    'sensors' => 'Sensors',
                    'locations' => 'Locations',
                    'alerts' => 'Alerts',
                    'manual_data' => 'Manual Data',
                    'reports' => 'Reports',
                    'settings' => 'Settings'
                ];
                
                // Show menu items based on user permissions
                foreach ($allMenuItems as $key => $name):
                    if ($_SESSION['role'] === 'admin' || 
                        (isset($_SESSION['permissions']) && in_array($key, $_SESSION['permissions']))):
                        $isActive = ($page == $key . '.php') ? 'active' : '';
                ?>
                <li><a href="<?= $key ?>.php" class="<?= $isActive ?>"><?= $name ?></a></li>
                <?php 
                    endif;
                endforeach; 
                ?>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>