-- =====================================================
-- COMPLETE DATABASE SETUP FOR IOT AIR PURIFIER PROJECT
-- =====================================================
-- This script creates all necessary tables and sample data
-- Run this in phpMyAdmin to set up your complete database

-- Create database (uncomment if you need to create it)
-- CREATE DATABASE IF NOT EXISTS `iot_air_purifier` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `iot_air_purifier`;

-- =====================================================
-- 1. USERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL UNIQUE,
    `email` varchar(100) NOT NULL UNIQUE,
    `password_hash` varchar(255) NOT NULL,
    `full_name` varchar(100),
    `phone` varchar(20),
    `role` enum('admin', 'user', 'viewer') DEFAULT 'user',
    `is_active` boolean DEFAULT true,
    `last_login` timestamp NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_username` (`username`),
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. LOCATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `locations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `type` enum('industrial', 'residential', 'commercial', 'rural') NOT NULL,
    `address` text,
    `latitude` decimal(10, 8),
    `longitude` decimal(11, 8),
    `priority` enum('low', 'medium', 'high') DEFAULT 'medium',
    `radius` decimal(5, 2) DEFAULT 2.0,
    `description` text,
    `considerations` json,
    `is_active` boolean DEFAULT true,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_name` (`name`),
    INDEX `idx_type` (`type`),
    INDEX `idx_priority` (`priority`),
    INDEX `idx_coordinates` (`latitude`, `longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. DEVICES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `devices` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `device_id` varchar(50) NOT NULL UNIQUE,
    `name` varchar(100) NOT NULL,
    `location_id` int(11),
    `location` varchar(100), -- Keeping for backward compatibility
    `status` enum('online', 'offline', 'maintenance') DEFAULT 'offline',
    `device_type` enum('sensor', 'purifier', 'monitor') DEFAULT 'sensor',
    `model` varchar(100),
    `manufacturer` varchar(100),
    `firmware_version` varchar(50),
    `last_active` timestamp NULL,
    `is_active` boolean DEFAULT true,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_device_id` (`device_id`),
    INDEX `idx_name` (`name`),
    INDEX `idx_location_id` (`location_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_device_type` (`device_type`),
    FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. SENSORS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `sensors` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `device_id` int(11) NOT NULL,
    `name` varchar(100) NOT NULL,
    `location_id` int(11),
    `sensor_type` enum('PM2.5', 'PM10', 'CO2', 'CO', 'NO2', 'SO2', 'temperature', 'humidity') NOT NULL,
    `unit` varchar(20) NOT NULL,
    `calibration_date` date,
    `next_calibration` date,
    `accuracy` decimal(5, 2),
    `range_min` decimal(10, 4),
    `range_max` decimal(10, 4),
    `interval` int(11) DEFAULT 60, -- Reading interval in seconds
    `protocol` varchar(50) DEFAULT 'HTTP',
    `status` enum('active', 'inactive', 'maintenance') DEFAULT 'active',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_device_id` (`device_id`),
    INDEX `idx_location_id` (`location_id`),
    INDEX `idx_sensor_type` (`sensor_type`),
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`device_id`) REFERENCES `devices`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. AIR QUALITY DATA TABLE (Main readings table)
-- =====================================================
CREATE TABLE IF NOT EXISTS `air_quality_data` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `device_id` int(11),
    `pm25` decimal(8, 2),
    `pm10` decimal(8, 2),
    `co2` decimal(8, 2),
    `co` decimal(8, 2),
    `no2` decimal(8, 2),
    `so2` decimal(8, 2),
    `temperature` decimal(5, 2),
    `humidity` decimal(5, 2),
    `aqi` int(11),
    `quality_level` enum('good', 'moderate', 'unhealthy', 'hazardous') DEFAULT 'good',
    `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_device_id` (`device_id`),
    INDEX `idx_timestamp` (`timestamp`),
    INDEX `idx_quality_level` (`quality_level`),
    INDEX `idx_pm25` (`pm25`),
    INDEX `idx_pm10` (`pm10`),
    INDEX `idx_co2` (`co2`),
    FOREIGN KEY (`device_id`) REFERENCES `devices`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. SENSOR READINGS TABLE (Individual sensor readings)
-- =====================================================
CREATE TABLE IF NOT EXISTS `sensor_readings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `sensor_id` int(11) NOT NULL,
    `location_id` int(11),
    `reading_value` decimal(10, 4) NOT NULL,
    `unit` varchar(20) NOT NULL,
    `reading_time` timestamp NOT NULL,
    `quality_level` enum('good', 'moderate', 'unhealthy', 'hazardous') DEFAULT 'good',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_sensor_id` (`sensor_id`),
    INDEX `idx_location_id` (`location_id`),
    INDEX `idx_reading_time` (`reading_time`),
    INDEX `idx_quality_level` (`quality_level`),
    FOREIGN KEY (`sensor_id`) REFERENCES `sensors`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. ALERTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `alerts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `location_id` int(11),
    `pollutant` enum('PM2.5', 'PM10', 'CO2', 'CO', 'NO2', 'SO2', 'AQI') NOT NULL,
    `moderate_level` decimal(10, 4),
    `unhealthy_level` decimal(10, 4),
    `hazardous_level` decimal(10, 4),
    `emergency_level` decimal(10, 4),
    `delivery_methods` json,
    `is_active` boolean DEFAULT true,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_location_id` (`location_id`),
    INDEX `idx_pollutant` (`pollutant`),
    INDEX `idx_is_active` (`is_active`),
    FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. MANUAL ENTRIES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `manual_entries` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `device_id` int(11),
    `pm25` decimal(8, 2),
    `pm10` decimal(8, 2),
    `co2` decimal(8, 2),
    `co` decimal(8, 2),
    `temperature` decimal(5, 2),
    `humidity` decimal(5, 2),
    `notes` text,
    `certificate_path` varchar(255),
    `entered_by` int(11),
    `entry_date` date,
    `entry_time` time,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_device_id` (`device_id`),
    INDEX `idx_entered_by` (`entered_by`),
    INDEX `idx_entry_date` (`entry_date`),
    FOREIGN KEY (`device_id`) REFERENCES `devices`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`entered_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. REPORTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `reports` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `type` enum('compliance', 'health', 'technical', 'custom') NOT NULL,
    `start_date` date NOT NULL,
    `end_date` date NOT NULL,
    `locations` json,
    `pollutants` json,
    `generated_by` int(11),
    `file_path` varchar(255),
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_date_range` (`start_date`, `end_date`),
    INDEX `idx_generated_by` (`generated_by`),
    FOREIGN KEY (`generated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. USER SETTINGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `user_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `setting_key` varchar(100) NOT NULL,
    `setting_value` text,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_setting` (`user_id`, `setting_key`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. NOTIFICATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `title` varchar(200) NOT NULL,
    `message` text NOT NULL,
    `type` enum('info', 'warning', 'error', 'success') DEFAULT 'info',
    `is_read` boolean DEFAULT false,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. DEVICE SETTINGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `device_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `device_id` int(11) NOT NULL,
    `setting_key` varchar(100) NOT NULL,
    `setting_value` text,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_device_setting` (`device_id`, `setting_key`),
    FOREIGN KEY (`device_id`) REFERENCES `devices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERT SAMPLE DATA
-- =====================================================

-- Insert default admin user (password: admin)
INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `role`, `phone`) VALUES
('admin', 'admin@airquality.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', '+880 1234-567890'),
('user1', 'user1@airquality.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'user', '+880 1234-567891'),
('viewer1', 'viewer1@airquality.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', 'viewer', '+880 1234-567892')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Insert sample locations
INSERT INTO `locations` (`name`, `type`, `address`, `latitude`, `longitude`, `priority`, `radius`, `description`, `considerations`) VALUES
('Dhaka Division', 'residential', 'Dhaka, Bangladesh', 23.8103, 90.4125, 'high', 5.0, 'Capital city with high population density', '["traffic", "construction", "seasonal"]'),
('Chittagong Division', 'industrial', 'Chittagong, Bangladesh', 22.3419, 91.8132, 'high', 3.0, 'Major port city with industrial activities', '["industrial", "traffic", "seasonal"]'),
('Sylhet Division', 'rural', 'Sylhet, Bangladesh', 24.8949, 91.8687, 'medium', 2.0, 'Agricultural area with tea gardens', '["seasonal", "agricultural"]'),
('Rajshahi Division', 'commercial', 'Rajshahi, Bangladesh', 24.3745, 88.6042, 'medium', 2.5, 'Commercial hub in northwest', '["traffic", "commercial"]'),
('Khulna Division', 'industrial', 'Khulna, Bangladesh', 22.8456, 89.5403, 'medium', 2.0, 'Industrial area with jute mills', '["industrial", "seasonal"]')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Insert sample devices
INSERT INTO `devices` (`device_id`, `name`, `location_id`, `location`, `status`, `device_type`, `model`, `manufacturer`, `firmware_version`, `last_active`) VALUES
('AQ_DHK_001', 'Dhaka Sensor 1', 1, 'Dhaka Division', 'online', 'sensor', 'AQ-2000', 'AirQuality Inc', 'v2.1.0', NOW() - INTERVAL 5 MINUTE),
('AQ_DHK_002', 'Dhaka Sensor 2', 1, 'Dhaka Division', 'online', 'sensor', 'AQ-2000', 'AirQuality Inc', 'v2.1.0', NOW() - INTERVAL 3 MINUTE),
('AQ_CTG_001', 'Chittagong Sensor 1', 2, 'Chittagong Division', 'online', 'sensor', 'AQ-2000', 'AirQuality Inc', 'v2.1.0', NOW() - INTERVAL 2 MINUTE),
('AQ_SYL_001', 'Sylhet Sensor 1', 3, 'Sylhet Division', 'maintenance', 'sensor', 'AQ-2000', 'AirQuality Inc', 'v2.0.5', NOW() - INTERVAL 1 DAY),
('AQ_RAJ_001', 'Rajshahi Sensor 1', 4, 'Rajshahi Division', 'offline', 'sensor', 'AQ-2000', 'AirQuality Inc', 'v2.1.0', NOW() - INTERVAL 2 DAY)
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Insert sample sensors
INSERT INTO `sensors` (`device_id`, `name`, `location_id`, `sensor_type`, `unit`, `calibration_date`, `next_calibration`, `accuracy`, `range_min`, `range_max`, `interval`, `protocol`, `status`) VALUES
(1, 'PM2.5 Sensor', 1, 'PM2.5', 'μg/m³', '2024-01-01', '2024-07-01', 95.5, 0.0, 1000.0, 60, 'HTTP', 'active'),
(1, 'PM10 Sensor', 1, 'PM10', 'μg/m³', '2024-01-01', '2024-07-01', 95.0, 0.0, 2000.0, 60, 'HTTP', 'active'),
(1, 'CO2 Sensor', 1, 'CO2', 'ppm', '2024-01-01', '2024-07-01', 98.0, 400.0, 5000.0, 60, 'HTTP', 'active'),
(1, 'Temperature Sensor', 1, 'temperature', '°C', '2024-01-01', '2024-07-01', 99.0, -40.0, 80.0, 60, 'HTTP', 'active'),
(1, 'Humidity Sensor', 1, 'humidity', '%', '2024-01-01', '2024-07-01', 97.0, 0.0, 100.0, 60, 'HTTP', 'active'),
(2, 'PM2.5 Sensor', 1, 'PM2.5', 'μg/m³', '2024-01-01', '2024-07-01', 95.5, 0.0, 1000.0, 60, 'HTTP', 'active'),
(3, 'PM2.5 Sensor', 2, 'PM2.5', 'μg/m³', '2024-01-01', '2024-07-01', 95.5, 0.0, 1000.0, 60, 'HTTP', 'active')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Insert sample air quality data
INSERT INTO `air_quality_data` (`device_id`, `pm25`, `pm10`, `co2`, `co`, `no2`, `so2`, `temperature`, `humidity`, `aqi`, `quality_level`, `timestamp`) VALUES
(1, 35.2, 68.7, 420.5, 0.8, 15.2, 8.5, 28.5, 65.0, 65, 'moderate', NOW() - INTERVAL 1 HOUR),
(1, 42.8, 75.3, 435.2, 1.2, 18.7, 9.2, 29.1, 62.5, 78, 'moderate', NOW() - INTERVAL 2 HOUR),
(1, 28.7, 55.4, 410.3, 0.5, 12.8, 6.2, 27.8, 68.2, 52, 'moderate', NOW() - INTERVAL 3 HOUR),
(2, 65.8, 112.4, 480.7, 2.5, 25.4, 18.9, 30.2, 58.7, 125, 'unhealthy', NOW() - INTERVAL 1 HOUR),
(2, 58.3, 98.7, 465.2, 2.1, 22.1, 16.5, 29.8, 60.1, 108, 'unhealthy', NOW() - INTERVAL 2 HOUR),
(3, 22.4, 45.6, 405.1, 0.3, 8.7, 4.1, 26.5, 72.3, 38, 'good', NOW() - INTERVAL 1 HOUR),
(3, 18.9, 38.2, 398.7, 0.2, 10.3, 5.8, 25.8, 75.1, 32, 'good', NOW() - INTERVAL 2 HOUR)
ON DUPLICATE KEY UPDATE `created_at` = CURRENT_TIMESTAMP;

-- Insert sample sensor readings
INSERT INTO `sensor_readings` (`sensor_id`, `location_id`, `reading_value`, `unit`, `reading_time`, `quality_level`) VALUES
(1, 1, 35.2, 'μg/m³', NOW() - INTERVAL 1 HOUR, 'moderate'),
(1, 1, 42.8, 'μg/m³', NOW() - INTERVAL 2 HOUR, 'moderate'),
(2, 1, 68.7, 'μg/m³', NOW() - INTERVAL 1 HOUR, 'moderate'),
(2, 1, 75.3, 'μg/m³', NOW() - INTERVAL 2 HOUR, 'moderate'),
(3, 1, 420.5, 'ppm', NOW() - INTERVAL 1 HOUR, 'good'),
(3, 1, 435.2, 'ppm', NOW() - INTERVAL 2 HOUR, 'good'),
(4, 1, 28.5, '°C', NOW() - INTERVAL 1 HOUR, 'good'),
(5, 1, 65.0, '%', NOW() - INTERVAL 1 HOUR, 'good')
ON DUPLICATE KEY UPDATE `created_at` = CURRENT_TIMESTAMP;

-- Insert sample alerts
INSERT INTO `alerts` (`location_id`, `pollutant`, `moderate_level`, `unhealthy_level`, `hazardous_level`, `emergency_level`, `delivery_methods`) VALUES
(1, 'PM2.5', 35.0, 55.0, 150.0, 250.0, '["email", "sms", "push"]'),
(1, 'PM10', 55.0, 155.0, 255.0, 355.0, '["email", "sms", "push"]'),
(1, 'CO2', 1000.0, 2000.0, 5000.0, 10000.0, '["email", "push"]'),
(2, 'PM2.5', 35.0, 55.0, 150.0, 250.0, '["email", "sms", "push"]'),
(3, 'PM2.5', 35.0, 55.0, 150.0, 250.0, '["email", "push"]')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Insert sample manual entries
INSERT INTO `manual_entries` (`device_id`, `pm25`, `pm10`, `co2`, `co`, `temperature`, `humidity`, `notes`, `entered_by`, `entry_date`, `entry_time`) VALUES
(1, 32.1, 65.3, 415.2, 0.7, 28.2, 66.5, 'Manual calibration reading', 1, CURDATE(), CURTIME()),
(2, 45.8, 78.9, 445.6, 1.8, 29.5, 61.2, 'Field verification', 1, CURDATE(), CURTIME()),
(3, 20.3, 42.1, 402.8, 0.4, 26.1, 73.8, 'Quality check', 2, CURDATE(), CURTIME())
ON DUPLICATE KEY UPDATE `created_at` = CURRENT_TIMESTAMP;

-- Insert sample reports
INSERT INTO `reports` (`type`, `start_date`, `end_date`, `locations`, `pollutants`, `generated_by`) VALUES
('compliance', CURDATE() - INTERVAL 30 DAY, CURDATE(), '[1,2,3]', '["PM2.5", "PM10", "CO2"]', 1),
('health', CURDATE() - INTERVAL 7 DAY, CURDATE(), '[1,2]', '["PM2.5", "PM10"]', 1),
('technical', CURDATE() - INTERVAL 90 DAY, CURDATE(), '[1,2,3,4,5]', '["PM2.5", "PM10", "CO2", "CO", "NO2", "SO2"]', 1)
ON DUPLICATE KEY UPDATE `created_at` = CURRENT_TIMESTAMP;

-- Insert sample user settings
INSERT INTO `user_settings` (`user_id`, `setting_key`, `setting_value`) VALUES
(1, 'theme', 'dark'),
(1, 'language', 'en'),
(1, 'notifications_email', 'true'),
(1, 'notifications_sms', 'false'),
(2, 'theme', 'light'),
(2, 'language', 'en'),
(2, 'notifications_email', 'true')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Insert sample device settings
INSERT INTO `device_settings` (`device_id`, `setting_key`, `setting_value`) VALUES
(1, 'reading_interval', '60'),
(1, 'alert_threshold_pm25', '35.0'),
(1, 'alert_threshold_pm10', '55.0'),
(2, 'reading_interval', '60'),
(2, 'alert_threshold_pm25', '35.0'),
(3, 'reading_interval', '120'),
(3, 'alert_threshold_pm25', '35.0')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Insert sample notifications
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`) VALUES
(1, 'System Update', 'Database has been successfully updated with new tables and sample data.', 'success'),
(1, 'New Device Online', 'Device AQ_DHK_002 is now online and collecting data.', 'info'),
(2, 'Welcome', 'Welcome to the Air Quality Monitoring System!', 'info')
ON DUPLICATE KEY UPDATE `created_at` = CURRENT_TIMESTAMP;

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Show all tables
SELECT 'Tables created successfully!' as status;
SELECT TABLE_NAME as table_name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME;

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================
SELECT '🎉 DATABASE SETUP COMPLETED SUCCESSFULLY! 🎉' as message;
SELECT 'Your IoT Air Purifier project database is now ready with:' as info;
SELECT '✅ 12 tables created' as detail;
SELECT '✅ Sample data inserted' as detail;
SELECT '✅ All relationships established' as detail;
SELECT '✅ Indexes and constraints added' as detail;
SELECT 'You can now run your PHP application!' as next_step;
