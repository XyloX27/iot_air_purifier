<?php
/**
 * Permissions Helper Functions
 * Handles user access control and permission checking
 */

/**
 * Check if current user has permission to access a specific section
 * @param string $section The section name to check (e.g., 'dashboard', 'sensors', 'reports')
 * @return bool True if user has permission, false otherwise
 */
function hasPermission($section) {
    // Admin has access to everything
    if ($_SESSION['role'] === 'admin') {
        return true;
    }
    
    // Check if user has specific permission
    if (isset($_SESSION['permissions']) && is_array($_SESSION['permissions'])) {
        return in_array($section, $_SESSION['permissions']);
    }
    
    // Default: no access
    return false;
}

/**
 * Check if current user can access the current page
 * Redirects to dashboard if no permission
 */
function checkPageAccess() {
    $currentPage = getCurrentPage();
    
    if (!hasPermission($currentPage)) {
        $_SESSION['error'] = "You don't have permission to access this page.";
        header('Location: dashboard.php');
        exit;
    }
}

/**
 * Get current page name from URL
 * @return string Current page name
 */
function getCurrentPage() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $pageName = basename($scriptName, '.php');
    
    // Map page names to permission names
    $pageMap = [
        'dashboard' => 'dashboard',
        'sensors' => 'sensors',
        'locations' => 'locations',
        'alerts' => 'alerts',
        'manual_data' => 'manual_data',
        'reports' => 'reports',
        'settings' => 'settings'
    ];
    
    return $pageMap[$pageName] ?? 'dashboard';
}

/**
 * Load user permissions into session
 * Call this after user login
 */
function loadUserPermissions($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT permission_name FROM user_permissions WHERE user_id = ?");
    $stmt->execute([$userId]);
    $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $_SESSION['permissions'] = $permissions;
}

/**
 * Get user's accessible menu items
 * @return array Array of menu items user can access
 */
function getAccessibleMenuItems() {
    $allMenuItems = [
        'dashboard' => ['name' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'],
        'sensors' => ['name' => 'Sensors', 'icon' => 'fas fa-microchip'],
        'locations' => ['name' => 'Locations', 'icon' => 'fas fa-map-marker-alt'],
        'alerts' => ['name' => 'Alerts', 'icon' => 'fas fa-bell'],
        'manual_data' => ['name' => 'Manual Data', 'icon' => 'fas fa-edit'],
        'reports' => ['name' => 'Reports', 'icon' => 'fas fa-chart-bar'],
        'settings' => ['name' => 'Settings', 'icon' => 'fas fa-cog']
    ];
    
    $accessibleItems = [];
    
    foreach ($allMenuItems as $key => $item) {
        if (hasPermission($key)) {
            $accessibleItems[$key] = $item;
        }
    }
    
    return $accessibleItems;
}

/**
 * Render navigation menu based on user permissions
 */
function renderNavigationMenu() {
    $menuItems = getAccessibleMenuItems();
    
    foreach ($menuItems as $key => $item) {
        $isActive = (basename($_SERVER['SCRIPT_NAME'], '.php') === $key) ? 'active' : '';
        $url = $key . '.php';
        
        echo '<a href="' . $url . '" class="nav-link ' . $isActive . '">';
        echo '<i class="' . $item['icon'] . '"></i>';
        echo '<span>' . $item['name'] . '</span>';
        echo '</a>';
    }
}
?>
