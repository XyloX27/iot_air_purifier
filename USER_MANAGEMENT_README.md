# 🔐 User Management System - AirQuality IoT

## Overview
The AirQuality IoT system now includes a comprehensive **Role-Based Access Control (RBAC)** system that allows administrators to create users with specific permissions and restrict access to different sections of the application.

## ✨ Features

### 🔑 User Roles
- **Admin**: Full access to all features and user management
- **User**: Standard user with customizable permissions
- **Viewer**: Limited access user for read-only operations

### 🎯 Granular Permissions
Users can be granted access to specific sections:
- ✅ Dashboard
- ✅ Sensors
- ✅ Locations
- ✅ Alerts
- ✅ Manual Data
- ✅ Reports
- ✅ Settings

### 🛡️ Security Features
- Session-based authentication
- Permission-based page access
- Role-based navigation menu
- Secure user creation and deletion

## 🚀 Quick Start

### 1. Database Setup
First, run the permissions table creation script:
```sql
-- Run this in your database
source create_user_permissions_table.sql
```

### 2. Create Test Users (Optional)
To see the system in action, create some test users:
```sql
-- Run this to create demo users
source create_test_users.sql
```

### 3. Access the System
- **Admin Login**: `admin` / `admin` (full access)
- **User1 Login**: `user1` / `user123` (sensors + locations only)
- **User2 Login**: `user2` / `user456` (alerts + manual data only)

## 📋 How to Use

### For Administrators

#### Adding New Users
1. **Login as admin** and go to **Settings** → **Users** tab
2. **Fill in user details**:
   - Username, Email, Full Name, Phone
   - Password and Role selection
3. **Select permissions** by checking the desired menu items
4. **Click "Add User"** to create the account

#### Example User Scenarios

**Scenario 1: Field Technician**
- **Role**: User
- **Permissions**: Sensors, Locations, Manual Data
- **Purpose**: Can monitor sensors, manage locations, and enter manual readings

**Scenario 2: Data Analyst**
- **Role**: User
- **Permissions**: Dashboard, Reports, Alerts
- **Purpose**: Can view analytics, generate reports, and manage alert thresholds

**Scenario 3: Read-Only Viewer**
- **Role**: Viewer
- **Permissions**: Dashboard only
- **Purpose**: Can view system status but cannot make changes

#### Managing Existing Users
- **View all users** in the Users table
- **See permissions** for each user
- **Delete users** (except your own account)
- **Monitor user activity** and creation dates

### For Regular Users

#### What You'll See
- **Limited navigation menu** based on your permissions
- **Access only to allowed sections**
- **No access to user management** (admin only)
- **Personalized dashboard** with relevant data

#### Example: User1 Experience
- **Login**: `user1` / `user123`
- **Navigation**: Only "Sensors" and "Locations" visible
- **Access**: Can view/manage sensors and locations
- **Restricted**: Cannot access alerts, reports, or settings

## 🔧 Technical Implementation

### Database Structure
```sql
-- Users table with roles
users: id, username, email, password_hash, full_name, role, phone, created_at

-- Permissions table for granular access
user_permissions: id, user_id, permission_name, created_at
```

### Permission System
```php
// Check if user has permission
if (hasPermission('sensors')) {
    // User can access sensors
}

// Get accessible menu items
$menuItems = getAccessibleMenuItems();

// Check page access
checkPageAccess();
```

### Session Management
```php
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];
$_SESSION['permissions'] = $permissions;
```

## 📱 User Interface

### Settings Page - Users Tab
- **Add New User Form**: Complete user creation interface
- **Permissions Grid**: Visual checkbox selection for menu access
- **Users Table**: Overview of all users and their permissions
- **Role Badges**: Color-coded role indicators
- **Permission Tags**: Visual representation of user access

### Navigation Menu
- **Dynamic rendering** based on user permissions
- **Active page highlighting**
- **Responsive design** for mobile devices

## 🚨 Security Considerations

### Best Practices
1. **Use strong passwords** for all accounts
2. **Regular permission reviews** for user accounts
3. **Monitor user activity** and access patterns
4. **Limit admin accounts** to necessary personnel only

### Production Recommendations
1. **Implement password hashing** (currently using plain text for demo)
2. **Add session timeout** and automatic logout
3. **Enable HTTPS** for secure data transmission
4. **Add audit logging** for user actions
5. **Implement two-factor authentication** for admin accounts

## 🐛 Troubleshooting

### Common Issues

#### User Can't Access Expected Pages
- **Check permissions**: Verify user has correct permissions in database
- **Clear session**: Logout and login again
- **Check role**: Ensure user role is properly set

#### Navigation Menu Missing Items
- **Verify permissions**: Check user_permissions table
- **Session refresh**: Logout/login to reload permissions
- **Database connection**: Ensure permissions are being loaded

#### Admin Can't See Users Tab
- **Check role**: Ensure user has 'admin' role
- **Session verification**: Verify $_SESSION['role'] is set
- **Database query**: Check if user data is being fetched

### Debug Commands
```sql
-- Check user permissions
SELECT u.username, u.role, GROUP_CONCAT(up.permission_name) as permissions
FROM users u
LEFT JOIN user_permissions up ON u.id = up.user_id
WHERE u.username = 'username_here'
GROUP BY u.id;

-- Verify admin users
SELECT username, role FROM users WHERE role = 'admin';

-- Check all permissions
SELECT * FROM user_permissions ORDER BY user_id;
```

## 🔄 Future Enhancements

### Planned Features
- **User groups** for easier permission management
- **Temporary permissions** with expiration dates
- **Permission inheritance** from roles
- **Advanced audit logging** for compliance
- **API key management** for external integrations
- **Multi-tenant support** for different organizations

### Customization Options
- **Custom permission names** for specific features
- **Time-based access** (e.g., business hours only)
- **Location-based restrictions** for mobile users
- **Integration with LDAP/Active Directory**

## 📞 Support

For technical support or questions about the user management system:
1. **Check this documentation** for common solutions
2. **Review database logs** for error messages
3. **Verify file permissions** and database connectivity
4. **Contact system administrator** for complex issues

---

**🎉 Congratulations!** You now have a fully functional role-based access control system for your AirQuality IoT application. Users can be created with specific permissions, ensuring secure and controlled access to different system features.
