<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$username, $email, $phone, $_SESSION['user_id']]);
        
        $_SESSION['username'] = $username;
        $success = "Profile updated successfully!";
    }
    
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if ($newPassword !== $confirmPassword) {
            $error = "New passwords don't match!";
        } else {
            // Simple password update - no hashing
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$newPassword, $_SESSION['user_id']]);
            
            $success = "Password changed successfully!";
        }
    }
    
    // Handle user management
    if (isset($_POST['add_user']) && $_SESSION['role'] === 'admin') {
        $newUsername = $_POST['new_username'];
        $newEmail = $_POST['new_email'];
        $newPassword = $_POST['new_password'];
        $newRole = $_POST['new_role'];
        $newFullName = $_POST['new_full_name'];
        $newPhone = $_POST['new_phone'] ?? '';
        
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$newUsername]);
        if ($stmt->fetch()) {
            $error = "Username already exists!";
        } else {
            // Create new user with simple password
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, role, phone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$newUsername, $newEmail, $newPassword, $newFullName, $newRole, $newPhone]);
            
            $newUserId = $pdo->lastInsertId();
            
            // Set user permissions (simplified - skip if table doesn't exist)
            if (isset($_POST['user_permissions'])) {
                $permissions = $_POST['user_permissions'];
                
                // Try to insert permissions, but don't fail if table doesn't exist
                foreach ($permissions as $permission) {
                    try {
                        $stmt = $pdo->prepare("INSERT IGNORE INTO user_permissions (user_id, permission_name) VALUES (?, ?)");
                        $stmt->execute([$newUserId, $permission]);
                    } catch (Exception $e) {
                        // Ignore permission errors - user is still created
                        break;
                    }
                }
            }
            
            $success = "User '$newUsername' added successfully!";
        }
    }
    
    if (isset($_POST['delete_user']) && $_SESSION['role'] === 'admin') {
        $userId = $_POST['user_id'];
        
        // Don't allow admin to delete themselves
        if ($userId == $_SESSION['user_id']) {
            $error = "You cannot delete your own account!";
        } else {
                    // Delete user permissions first (if table exists)
        try {
            $stmt = $pdo->prepare("DELETE FROM user_permissions WHERE user_id = ?");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            // Permissions table might not exist
        }
        
        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
            
            $success = "User deleted successfully!";
        }
    }
}

// Get current user data
$stmt = $pdo->prepare("SELECT username, email, phone, role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userData = $stmt->fetch();

// Get all users for admin management
$allUsers = [];
if ($_SESSION['role'] === 'admin') {
    try {
        $stmt = $pdo->query("
            SELECT u.*, GROUP_CONCAT(up.permission_name) as permissions 
            FROM users u 
            LEFT JOIN user_permissions up ON u.id = up.user_id 
            GROUP BY u.id 
            ORDER BY u.created_at DESC
        ");
        $allUsers = $stmt->fetchAll();
    } catch (Exception $e) {
        // If permissions table doesn't exist, just get users
        $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
        $allUsers = $stmt->fetchAll();
    }
}
?>

<div class="page-header">
    <h1>Settings</h1>
    <p>Manage your account and system preferences</p>
</div>

<div class="page-content">
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="settings-tabs">
        <div class="tab">
            <button class="tablinks active" onclick="openTab(event, 'Profile')">Profile</button>
            <button class="tablinks" onclick="openTab(event, 'Password')">Password</button>
            <button class="tablinks" onclick="openTab(event, 'Preferences')">Preferences</button>
            <button class="tablinks" onclick="openTab(event, 'Notifications')">Notifications</button>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <button class="tablinks" onclick="openTab(event, 'Users')">Users</button>
            <?php endif; ?>
        </div>
        
        <div id="Profile" class="tabcontent" style="display: block;">
            <h2>Profile Settings</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($userData['username']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>">
                </div>
                <button type="submit" name="update_profile">Update Profile</button>
            </form>
        </div>
        
        <div id="Password" class="tabcontent">
            <h2>Change Password</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password">Change Password</button>
            </form>
        </div>
        
        <div id="Preferences" class="tabcontent">
            <h2>System Preferences</h2>
            <form>
                <div class="form-group">
                    <label for="language">Language</label>
                    <select id="language" name="language">
                        <option value="english">English</option>
                        <option value="bengali">Bengali</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="timezone">Timezone</label>
                    <select id="timezone" name="timezone">
                        <option value="Asia/Dhaka" selected>Asia/Dhaka (GMT+6)</option>
                        <option value="UTC">UTC</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_format">Date Format</label>
                    <select id="date_format" name="date_format">
                        <option value="Y-m-d">YYYY-MM-DD</option>
                        <option value="d-m-Y">DD-MM-YYYY</option>
                        <option value="m/d/Y">MM/DD/YYYY</option>
                    </select>
                </div>
                <button type="submit">Save Preferences</button>
            </form>
        </div>
        
        <div id="Notifications" class="tabcontent">
            <h2>Notification Preferences</h2>
            <form>
                <div class="form-group">
                    <label>Alert Types</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="alert_types[]" value="moderate" checked> Moderate Level Alerts</label>
                        <label><input type="checkbox" name="alert_types[]" value="unhealthy" checked> Unhealthy Level Alerts</label>
                        <label><input type="checkbox" name="alert_types[]" value="hazardous" checked> Hazardous Level Alerts</label>
                        <label><input type="checkbox" name="alert_types[]" value="emergency" checked> Emergency Level Alerts</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Notification Methods</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="notification_methods[]" value="email" checked> Email</label>
                        <label><input type="checkbox" name="notification_methods[]" value="sms"> SMS</label>
                        <label><input type="checkbox" name="notification_methods[]" value="push" checked> Push Notification</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notification_frequency">Notification Frequency</label>
                    <select id="notification_frequency" name="notification_frequency">
                        <option value="immediately">Immediately</option>
                        <option value="hourly">Hourly Summary</option>
                        <option value="daily">Daily Summary</option>
                    </select>
                </div>
                
                <button type="submit">Save Notification Settings</button>
            </form>
        </div>
        
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <div id="Users" class="tabcontent">
            <h2>User Management</h2>
            
            <!-- Add New User Form -->
            <div class="card">
                <h3>Add New User</h3>
                <form method="POST" class="user-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_username">Username</label>
                            <input type="text" id="new_username" name="new_username" required>
                        </div>
                        <div class="form-group">
                            <label for="new_email">Email</label>
                            <input type="email" id="new_email" name="new_email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_full_name">Full Name</label>
                            <input type="text" id="new_full_name" name="new_full_name" required>
                        </div>
                        <div class="form-group">
                            <label for="new_phone">Phone Number</label>
                            <input type="tel" id="new_phone" name="new_phone">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_role">Role</label>
                            <select id="new_role" name="new_role" required>
                                <option value="user">User</option>
                                <option value="viewer">Viewer</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Menu Access Permissions</label>
                        <div class="permissions-grid">
                            <label class="permission-item">
                                <input type="checkbox" name="user_permissions[]" value="dashboard">
                                <span class="permission-label">Dashboard</span>
                            </label>
                            <label class="permission-item">
                                <input type="checkbox" name="user_permissions[]" value="sensors">
                                <span class="permission-label">Sensors</span>
                            </label>
                            <label class="permission-item">
                                <input type="checkbox" name="user_permissions[]" value="locations">
                                <span class="permission-label">Locations</span>
                            </label>
                            <label class="permission-item">
                                <input type="checkbox" name="user_permissions[]" value="alerts">
                                <span class="permission-label">Alerts</span>
                            </label>
                            <label class="permission-item">
                                <input type="checkbox" name="user_permissions[]" value="manual_data">
                                <span class="permission-label">Manual Data</span>
                            </label>
                            <label class="permission-item">
                                <input type="checkbox" name="user_permissions[]" value="reports">
                                <span class="permission-label">Reports</span>
                            </label>
                            <label class="permission-item">
                                <input type="checkbox" name="user_permissions[]" value="settings">
                                <span class="permission-label">Settings</span>
                            </label>
                        </div>
                        <small>Select which menu items this user can access</small>
                    </div>
                    
                    <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                </form>
            </div>
            
            <!-- Users List -->
            <div class="card">
                <h3>Existing Users</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Permissions</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $user): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($user['username']) ?></strong>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="badge badge-primary">You</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($user['full_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="role-badge role-<?= $user['role'] ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['permissions']): ?>
                                        <div class="permission-tags">
                                            <?php 
                                            $permissions = explode(',', $user['permissions']);
                                            foreach ($permissions as $perm): 
                                            ?>
                                                <span class="permission-tag"><?= ucfirst($perm) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="no-permissions">No specific permissions</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" name="delete_user" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-muted">Cannot delete own account</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}
</script>

<?php require_once 'includes/footer.php'; ?>