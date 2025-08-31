-- Create user_permissions table for role-based access control
CREATE TABLE IF NOT EXISTS `user_permissions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `permission_name` varchar(50) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_permission` (`user_id`, `permission_name`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_permission_name` (`permission_name`),
    CONSTRAINT `fk_user_permissions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some default permissions for existing admin user
INSERT IGNORE INTO `user_permissions` (`user_id`, `permission_name`) 
SELECT u.id, 'dashboard' FROM users u WHERE u.role = 'admin'
UNION ALL
SELECT u.id, 'sensors' FROM users u WHERE u.role = 'admin'
UNION ALL
SELECT u.id, 'locations' FROM users u WHERE u.role = 'admin'
UNION ALL
SELECT u.id, 'alerts' FROM users u WHERE u.role = 'admin'
UNION ALL
SELECT u.id, 'manual_data' FROM users u WHERE u.role = 'admin'
UNION ALL
SELECT u.id, 'reports' FROM users u WHERE u.role = 'admin'
UNION ALL
SELECT u.id, 'settings' FROM users u WHERE u.role = 'admin';

-- Update existing users to have admin role if they don't have one
UPDATE users SET role = 'admin' WHERE role IS NULL OR role = '';
