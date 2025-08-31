# IoT Air Purifier - Setup Guide

## Quick Fix for Database Errors

The errors you're seeing are because the database tables don't exist yet. Here's how to fix it:

### Step 1: Start XAMPP
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Make sure both show green status

### Step 2: Create Database Tables
You have two options:

#### Option A: Using phpMyAdmin (Recommended)
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click "New" on the left sidebar
3. Enter database name: `iot_air_purifier`
4. Click "Create"
5. Select the `iot_air_purifier` database
6. Click the "SQL" tab
7. Copy and paste the entire content of `database_setup.sql`
8. Click "Go" to execute the script

#### Option B: Using MySQL Command Line
1. Open Command Prompt/Terminal
2. Navigate to MySQL bin directory (usually `C:\xampp\mysql\bin`)
3. Run: `mysql -u root -p`
4. Enter password (usually empty for XAMPP)
5. Copy and paste the content of `database_setup.sql`

### Step 3: Test Connection
1. Visit: `http://localhost/iot_air_purifier/db_test.php`
2. You should see green checkmarks for all tables
3. If successful, you can now access the main application

### Step 4: Access Application
1. Go to: `http://localhost/iot_air_purifier/`
2. You'll be redirected to the login page
3. Use these default credentials:
   - **Username:** `admin`
   - **Password:** `admin123`

## Default Database Credentials
- **Host:** localhost
- **Database:** iot_air_purifier
- **Username:** root
- **Password:** (empty)

## Troubleshooting

### If MySQL won't start:
- Check if port 3306 is already in use
- Restart XAMPP completely
- Check XAMPP error logs

### If tables still don't exist:
- Make sure you're in the right database
- Check for SQL syntax errors in phpMyAdmin
- Verify all SQL commands executed successfully

### If connection still fails:
- Check `config/database.php` file
- Verify database name matches exactly
- Make sure MySQL service is running

## File Structure
```
iot_air_purifier/
├── config/
│   └── database.php          # Database connection
├── css/                      # All your CSS files
├── database_setup.sql        # Database creation script
├── db_test.php              # Database connection test
├── index.php                # Main entry point
├── login.php                # Login page
├── dashboard.php            # Dashboard
├── devices.php              # Device management
├── sensors.php              # Sensor configuration
├── locations.php            # Location management
├── alerts.php               # Alert configuration
├── manual_data.php          # Manual data entry
├── reports.php              # Report generation
├── settings.php             # User settings
└── SETUP_GUIDE.md           # This file
```

## After Setup
Once the database is working:
1. All pages should load without errors
2. You can add/edit/delete devices, sensors, and locations
3. The modern CSS styling will be visible
4. All functionality will work properly

## Need Help?
If you still have issues:
1. Check the `db_test.php` page for specific error messages
2. Verify XAMPP is running correctly
3. Make sure all SQL commands executed successfully
4. Check that the database name matches exactly in all files
