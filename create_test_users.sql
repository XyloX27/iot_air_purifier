-- Create test users with different permissions
-- This demonstrates the role-based access control system

-- Create a regular user with limited access
INSERT INTO users (username, email, password_hash, full_name, role, phone) VALUES 
('user1', 'user1@airquality.com', 'user123', 'John Doe', 'user', '+880 1234-567891');

-- Create a viewer user with very limited access
INSERT INTO users (username, email, password_hash, full_name, role, phone) VALUES 
('user2', 'user2@airquality.com', 'user456', 'Jane Smith', 'viewer', '+880 1234-567892');

-- Get the user IDs
SET @user1_id = LAST_INSERT_ID();
SET @user2_id = (SELECT id FROM users WHERE username = 'user2');

-- Give user1 access to sensors and locations only
INSERT INTO user_permissions (user_id, permission_name) VALUES 
(@user1_id, 'sensors'),
(@user1_id, 'locations');

-- Give user2 access to alerts and manual data only
INSERT INTO user_permissions (user_id, permission_name) VALUES 
(@user2_id, 'alerts'),
(@user2_id, 'manual_data');

-- Display the created users and their permissions
SELECT 
    u.username,
    u.full_name,
    u.role,
    GROUP_CONCAT(up.permission_name) as permissions
FROM users u
LEFT JOIN user_permissions up ON u.id = up.user_id
WHERE u.username IN ('user1', 'user2')
GROUP BY u.id;
