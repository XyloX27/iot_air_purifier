-- IoT Air Purifier Database Setup Script
-- Run this script in phpMyAdmin or MySQL command line to create all necessary tables

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `iot_air_purifier` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `iot_air_purifier`;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL UNIQUE,
    `email` varchar(100) NOT NULL UNIQUE,
    `password_hash` varchar(255) NOT NULL,
    `full_name` varchar(100) NOT NULL,
    `role` enum('admin', 'user', 'viewer') DEFAULT 'user',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Devices table for IoT devices
CREATE TABLE IF NOT EXISTS `devices` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `device_id` varchar(50) NOT NULL UNIQUE,
    `name` varchar(100) NOT NULL,
    `type` enum('air_purifier', 'sensor', 'controller') NOT NULL,
    `model` varchar(100),
    `manufacturer` varchar(100),
    `location_id` int(11),
    `status` enum('online', 'offline', 'maintenance', 'error') DEFAULT 'offline',
    `last_seen` timestamp NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_device_id` (`device_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sensors table for IoT sensors
CREATE TABLE IF NOT EXISTS `sensors` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `sensor_id` varchar(50) NOT NULL UNIQUE,
    `name` varchar(100) NOT NULL,
    `type` enum('PM2.5', 'PM10', 'CO2', 'CO', 'NO2', 'SO2', 'AQI', 'temperature', 'humidity') NOT NULL,
    `device_id` int(11),
    `location_id` int(11),
    `unit` varchar(20) NOT NULL,
    `calibration_date` date,
    `status` enum('active', 'inactive', 'calibrating', 'error') DEFAULT 'active',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_sensor_id` (`sensor_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`device_id`) REFERENCES `devices`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Locations table for monitoring areas
CREATE TABLE IF NOT EXISTS `locations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `type` enum('residential', 'commercial', 'industrial', 'rural') NOT NULL,
    `address` text,
    `latitude` decimal(10, 8),
    `longitude` decimal(11, 8),
    `priority` enum('low', 'medium', 'high') DEFAULT 'medium',
    `description` text,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sensor readings table for air quality data
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

-- Alerts table for air quality alerts
CREATE TABLE IF NOT EXISTS `alerts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `pollutant_type` enum('PM2.5', 'PM10', 'CO2', 'CO', 'NO2', 'SO2', 'AQI') NOT NULL,
    `threshold_moderate` decimal(10, 4),
    `threshold_unhealthy` decimal(10, 4),
    `threshold_hazardous` decimal(10, 4),
    `threshold_emergency` decimal(10, 4),
    `delivery_methods` json,
    `location_id` int(11),
    `is_active` boolean DEFAULT true,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_pollutant_type` (`pollutant_type`),
    INDEX `idx_location_id` (`location_id`),
    INDEX `idx_is_active` (`is_active`),
    FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Manual data entries table
CREATE TABLE IF NOT EXISTS `manual_data` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `device_id` int(11),
    `location_id` int(11),
    `pollutant_type` enum('PM2.5', 'PM10', 'CO2', 'CO', 'NO2', 'SO2', 'AQI') NOT NULL,
    `reading_value` decimal(10, 4) NOT NULL,
    `unit` varchar(20) NOT NULL,
    `reading_time` timestamp NOT NULL,
    `notes` text,
    `certificate_file` varchar(255),
    `data_quality` enum('good', 'moderate', 'poor') DEFAULT 'good',
    `created_by` int(11),
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_device_id` (`device_id`),
    INDEX `idx_location_id` (`location_id`),
    INDEX `idx_pollutant_type` (`pollutant_type`),
    INDEX `idx_reading_time` (`reading_time`),
    FOREIGN KEY (`device_id`) REFERENCES `devices`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reports table for generated reports
CREATE TABLE IF NOT EXISTS `reports` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `report_name` varchar(100) NOT NULL,
    `report_type` enum('compliance', 'health', 'technical', 'custom') NOT NULL,
    `date_range_start` date NOT NULL,
    `date_range_end` date NOT NULL,
    `locations` json,
    `pollutants` json,
    `report_data` json,
    `generated_by` int(11),
    `generated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `file_path` varchar(255),
    PRIMARY KEY (`id`),
    INDEX `idx_report_type` (`report_type`),
    INDEX `idx_date_range` (`date_range_start`, `date_range_end`),
    INDEX `idx_generated_by` (`generated_by`),
    FOREIGN KEY (`generated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table for user preferences
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

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `role`) VALUES
('admin', 'admin@airquality.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Insert sample location
INSERT INTO `locations` (`name`, `type`, `address`, `latitude`, `longitude`, `priority`, `description`) VALUES
('Main Office', 'commercial', '123 Business Street, City Center', 40.7128, -74.0060, 'high', 'Primary office location with high foot traffic')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Insert sample device
INSERT INTO `devices` (`device_id`, `name`, `type`, `model`, `manufacturer`, `location_id`, `status`) VALUES
('DEV001', 'Air Quality Monitor 1', 'sensor', 'AQM-2000', 'AirTech Industries', 1, 'online')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Insert sample sensor
INSERT INTO `sensors` (`sensor_id`, `name`, `type`, `device_id`, `location_id`, `unit`, `status`) VALUES
('SENS001', 'PM2.5 Sensor', 'PM2.5', 1, 1, 'μg/m³', 'active')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Insert sample alert
INSERT INTO `alerts` (`name`, `pollutant_type`, `threshold_moderate`, `threshold_unhealthy`, `threshold_hazardous`, `threshold_emergency`, `delivery_methods`, `location_id`) VALUES
('PM2.5 Alert', 'PM2.5', 12.0, 35.4, 55.4, 150.4, '["email", "sms"]', 1)
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;
