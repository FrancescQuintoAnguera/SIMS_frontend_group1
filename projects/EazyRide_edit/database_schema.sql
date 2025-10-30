-- ============================================================================
-- VoltiaCar - Complete Database Schema
-- Carsharing Service Application
-- ============================================================================
-- This script creates the complete database schema for the VoltiaCar application
-- including all tables, indexes, and sample data needed for deployment.
--
-- Database: MariaDB 10.5+ or MySQL 8.0+
-- Character Set: utf8mb4
-- Collation: utf8mb4_unicode_ci
-- ============================================================================

-- Create database
CREATE DATABASE IF NOT EXISTS simsdb;
USE simsdb;

-- Drop existing tables (in order of dependencies)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS vehicle_usage;
DROP TABLE IF EXISTS subscriptions;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS nationalities;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- TABLE: nationalities
-- Stores available nationalities for user profiles
-- ============================================================================
CREATE TABLE nationalities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: users
-- Stores user account information and profile data
-- ============================================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(50),
    phone VARCHAR(20),
    birth_date DATE,
    sex ENUM('M', 'F', 'O') DEFAULT NULL, 
    address VARCHAR(255),                       
    dni VARCHAR(20), 
    is_admin BOOLEAN NOT NULL DEFAULT FALSE,
    iban VARCHAR(34),
    driver_license_photo VARCHAR(255),
    nationality_id INT,
    minute_balance INT DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nationality_id) REFERENCES nationalities(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_is_admin (is_admin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: subscriptions
-- Stores user subscription plans (basic/premium)
-- ============================================================================
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('basic', 'premium') NOT NULL DEFAULT 'basic',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    free_minutes INT DEFAULT 25,
    unlock_fee_waived BOOLEAN DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: vehicles
-- Stores vehicle information including location and availability
-- ============================================================================
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plate VARCHAR(20) NOT NULL UNIQUE,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT,
    battery_level INT DEFAULT 100,
    latitude DECIMAL(10,8) DEFAULT 41.3851,
    longitude DECIMAL(11,8) DEFAULT 2.1734,
    status ENUM('available', 'in_use', 'maintenance', 'reserved') DEFAULT 'available',
    vehicle_type VARCHAR(50) DEFAULT 'electric',
    is_accessible BOOLEAN DEFAULT FALSE,
    accessibility_features JSON,
    price_per_minute DECIMAL(5,2) DEFAULT 0.30,
    image_url VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_plate (plate),
    INDEX idx_brand_model (brand, model),
    INDEX idx_status (status),
    INDEX idx_location (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: locations
-- Stores predefined locations/stations for vehicles
-- ============================================================================
CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    address VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_coordinates (latitude, longitude),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: bookings
-- Stores vehicle reservations/bookings made by users
-- ============================================================================
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'confirmed',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_datetime (start_datetime, end_datetime),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: vehicle_usage
-- Stores actual vehicle usage history (trips)
-- ============================================================================
CREATE TABLE vehicle_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME,
    start_location_id INT,
    end_location_id INT,
    total_distance_km DECIMAL(8,2),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (start_location_id) REFERENCES locations(id) ON DELETE SET NULL,
    FOREIGN KEY (end_location_id) REFERENCES locations(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_start_time (start_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: payments
-- Stores payment transactions for vehicle usage and subscriptions
-- ============================================================================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_usage_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    type ENUM('unlock', 'time', 'subscription') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_usage_id) REFERENCES vehicle_usage(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SAMPLE DATA
-- ============================================================================

-- Insert sample nationalities
INSERT INTO nationalities (name) VALUES
('Spain'),
('France'),
('Germany'),
('Italy'),
('United Kingdom'),
('Portugal'),
('Netherlands'),
('Belgium'),
('Sweden'),
('Norway'),
('Denmark'),
('Finland'),
('Poland'),
('Romania'),
('Greece');

-- Insert sample locations (Barcelona area)
INSERT INTO locations (name, latitude, longitude, address) VALUES
('Plaça Catalunya', 41.38706100, 2.17009700, 'Plaça de Catalunya, Barcelona'),
('Sagrada Família', 41.40362400, 2.17432500, 'Carrer de Mallorca, 401, Barcelona'),
('Park Güell', 41.41449500, 2.15268900, 'Carrer d''Olot, s/n, Barcelona'),
('Camp Nou', 41.38087900, 2.12282700, 'C. d''Aristides Maillol, Barcelona'),
('Barceloneta Beach', 41.37545400, 2.18966700, 'Passeig Marítim de la Barceloneta, Barcelona'),
('Montjuïc', 41.36388900, 2.16500000, 'Parc de Montjuïc, Barcelona'),
('Gothic Quarter', 41.38250000, 2.17666700, 'Barri Gòtic, Barcelona'),
('Passeig de Gràcia', 41.39166700, 2.16444400, 'Passeig de Gràcia, Barcelona');

-- Insert sample vehicles with realistic data
INSERT INTO vehicles (plate, brand, model, year, battery_level, latitude, longitude, status, vehicle_type, is_accessible, price_per_minute, image_url) VALUES
('1234ABC', 'Tesla', 'Model 3', 2023, 95, 41.38706100, 2.17009700, 'available', 'electric', FALSE, 0.35, '/images/vehicles/tesla-model3.jpg'),
('5678DEF', 'Nissan', 'Leaf', 2022, 87, 41.40362400, 2.17432500, 'available', 'electric', TRUE, 0.30, '/images/vehicles/nissan-leaf.jpg'),
('9012GHI', 'Renault', 'Zoe', 2023, 92, 41.41449500, 2.15268900, 'available', 'electric', FALSE, 0.28, '/images/vehicles/renault-zoe.jpg'),
('3456JKL', 'BMW', 'i3', 2022, 78, 41.38087900, 2.12282700, 'available', 'electric', TRUE, 0.32, '/images/vehicles/bmw-i3.jpg'),
('7890MNO', 'Volkswagen', 'ID.3', 2023, 100, 41.37545400, 2.18966700, 'available', 'electric', FALSE, 0.30, '/images/vehicles/vw-id3.jpg'),
('2468PQR', 'Hyundai', 'Kona Electric', 2023, 85, 41.36388900, 2.16500000, 'available', 'electric', TRUE, 0.29, '/images/vehicles/hyundai-kona.jpg'),
('1357STU', 'Peugeot', 'e-208', 2022, 90, 41.38250000, 2.17666700, 'available', 'electric', FALSE, 0.27, '/images/vehicles/peugeot-e208.jpg'),
('9753VWX', 'Fiat', '500e', 2023, 88, 41.39166700, 2.16444400, 'available', 'electric', FALSE, 0.25, '/images/vehicles/fiat-500e.jpg');

-- Create admin user (password: admin123 - hashed with PASSWORD_DEFAULT)
-- Note: In production, change this password immediately after deployment
INSERT INTO users (email, username, password, sex, dni, address, fullname, is_admin, minute_balance, created_at) VALUES
('admin@voltiacar.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'N/A', '00000000A', 'N/A', 'System Administrator', TRUE, 0, NOW());
-- Success message
SELECT 'Database schema created successfully!' AS message;
SELECT CONCAT('Total tables created: ', COUNT(*)) AS tables_count 
FROM information_schema.tables 
WHERE table_schema = 'simsdb';
