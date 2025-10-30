-- ============================================================================
-- EazyRide Multi-Tenant Architecture - Database Schema
-- ============================================================================
-- This script converts the single-tenant system to a multi-tenant architecture
-- allowing multiple companies/organizations to use the same platform
-- ============================================================================

USE simsdb;

-- ============================================================================
-- TABLE: tenants
-- Stores information about each company/organization using the platform
-- ============================================================================
CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    subdomain VARCHAR(50) UNIQUE NOT NULL,
    domain VARCHAR(100) UNIQUE,
    logo_url VARCHAR(255),
    primary_color VARCHAR(7) DEFAULT '#007bff',
    secondary_color VARCHAR(7) DEFAULT '#6c757d',
    contact_email VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20),
    address VARCHAR(255),
    city VARCHAR(100),
    country VARCHAR(100),
    timezone VARCHAR(50) DEFAULT 'UTC',
    currency VARCHAR(3) DEFAULT 'EUR',
    language VARCHAR(5) DEFAULT 'es',
    status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
    settings JSON COMMENT 'Custom settings for the tenant',
    subscription_plan ENUM('basic', 'professional', 'enterprise') DEFAULT 'basic',
    subscription_start DATE,
    subscription_end DATE,
    max_vehicles INT DEFAULT 50,
    max_users INT DEFAULT 1000,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_subdomain (subdomain),
    INDEX idx_status (status),
    INDEX idx_subscription (subscription_plan, subscription_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: tenant_admins
-- Super administrators who can manage multiple tenants
-- ============================================================================
CREATE TABLE IF NOT EXISTS tenant_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    is_super_admin BOOLEAN DEFAULT FALSE,
    last_login DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_super_admin (is_super_admin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: tenant_admin_access
-- Maps which tenant admins have access to which tenants
-- ============================================================================
CREATE TABLE IF NOT EXISTS tenant_admin_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_admin_id INT NOT NULL,
    tenant_id INT NOT NULL,
    role ENUM('owner', 'admin', 'viewer') DEFAULT 'admin',
    granted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    granted_by INT COMMENT 'ID of the admin who granted access',
    FOREIGN KEY (tenant_admin_id) REFERENCES tenant_admins(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_admin_tenant (tenant_admin_id, tenant_id),
    INDEX idx_tenant_admin (tenant_admin_id),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Add tenant_id to existing tables
-- ============================================================================

-- Add tenant_id to users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS tenant_id INT AFTER id,
ADD CONSTRAINT fk_users_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
ADD INDEX idx_tenant_id (tenant_id);

-- Add tenant_id to vehicles table
ALTER TABLE vehicles 
ADD COLUMN IF NOT EXISTS tenant_id INT AFTER id,
ADD CONSTRAINT fk_vehicles_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
ADD INDEX idx_tenant_id (tenant_id);

-- Add tenant_id to locations table
ALTER TABLE locations 
ADD COLUMN IF NOT EXISTS tenant_id INT AFTER id,
ADD CONSTRAINT fk_locations_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
ADD INDEX idx_tenant_id (tenant_id);

-- Add tenant_id to bookings table
ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS tenant_id INT AFTER id,
ADD CONSTRAINT fk_bookings_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
ADD INDEX idx_tenant_id (tenant_id);

-- Add tenant_id to subscriptions table
ALTER TABLE subscriptions 
ADD COLUMN IF NOT EXISTS tenant_id INT AFTER id,
ADD CONSTRAINT fk_subscriptions_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
ADD INDEX idx_tenant_id (tenant_id);

-- Add tenant_id to vehicle_usage table
ALTER TABLE vehicle_usage 
ADD COLUMN IF NOT EXISTS tenant_id INT AFTER id,
ADD CONSTRAINT fk_vehicle_usage_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
ADD INDEX idx_tenant_id (tenant_id);

-- Add tenant_id to payments table
ALTER TABLE payments 
ADD COLUMN IF NOT EXISTS tenant_id INT AFTER id,
ADD CONSTRAINT fk_payments_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
ADD INDEX idx_tenant_id (tenant_id);

-- ============================================================================
-- TABLE: tenant_analytics
-- Stores usage analytics per tenant
-- ============================================================================
CREATE TABLE IF NOT EXISTS tenant_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    date DATE NOT NULL,
    total_bookings INT DEFAULT 0,
    total_revenue DECIMAL(10,2) DEFAULT 0.00,
    total_distance_km DECIMAL(10,2) DEFAULT 0.00,
    active_users INT DEFAULT 0,
    active_vehicles INT DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_date (tenant_id, date),
    INDEX idx_tenant_date (tenant_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: tenant_billing
-- Tracks billing information for each tenant
-- ============================================================================
CREATE TABLE IF NOT EXISTS tenant_billing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    billing_period_start DATE NOT NULL,
    billing_period_end DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    commission_percentage DECIMAL(5,2) DEFAULT 10.00,
    platform_fee DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'paid', 'overdue', 'cancelled') DEFAULT 'pending',
    paid_at DATETIME,
    invoice_url VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (status),
    INDEX idx_billing_period (billing_period_start, billing_period_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Insert default super admin
-- Password: superadmin123 (change immediately in production)
-- ============================================================================
INSERT INTO tenant_admins (email, username, password, fullname, is_super_admin) VALUES
('superadmin@eazyride.com', 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrator', TRUE)
ON DUPLICATE KEY UPDATE email=email;

-- ============================================================================
-- Insert sample tenant (Demo Company)
-- ============================================================================
INSERT INTO tenants (
    name, subdomain, contact_email, contact_phone, city, country, 
    subscription_plan, subscription_start, subscription_end, status
) VALUES (
    'Demo CarSharing Company', 
    'demo', 
    'demo@eazyride.com', 
    '+34 900 000 000',
    'Barcelona', 
    'Spain',
    'professional',
    CURDATE(),
    DATE_ADD(CURDATE(), INTERVAL 1 YEAR),
    'active'
) ON DUPLICATE KEY UPDATE name=name;

-- ============================================================================
-- Migrate existing data to default tenant
-- ============================================================================
SET @default_tenant_id = (SELECT id FROM tenants WHERE subdomain = 'demo' LIMIT 1);

UPDATE users SET tenant_id = @default_tenant_id WHERE tenant_id IS NULL;
UPDATE vehicles SET tenant_id = @default_tenant_id WHERE tenant_id IS NULL;
UPDATE locations SET tenant_id = @default_tenant_id WHERE tenant_id IS NULL;
UPDATE bookings SET tenant_id = @default_tenant_id WHERE tenant_id IS NULL;
UPDATE subscriptions SET tenant_id = @default_tenant_id WHERE tenant_id IS NULL;
UPDATE vehicle_usage SET tenant_id = @default_tenant_id WHERE tenant_id IS NULL;
UPDATE payments SET tenant_id = @default_tenant_id WHERE tenant_id IS NULL;

-- ============================================================================
-- Grant super admin access to demo tenant
-- ============================================================================
INSERT INTO tenant_admin_access (tenant_admin_id, tenant_id, role)
SELECT ta.id, t.id, 'owner'
FROM tenant_admins ta, tenants t
WHERE ta.username = 'superadmin' AND t.subdomain = 'demo'
ON DUPLICATE KEY UPDATE role=role;

-- ============================================================================
-- Create views for easier querying
-- ============================================================================

-- View: Active tenants with statistics
CREATE OR REPLACE VIEW v_active_tenants AS
SELECT 
    t.id,
    t.name,
    t.subdomain,
    t.contact_email,
    t.subscription_plan,
    t.subscription_end,
    COUNT(DISTINCT u.id) as total_users,
    COUNT(DISTINCT v.id) as total_vehicles,
    COUNT(DISTINCT b.id) as total_bookings,
    t.created_at
FROM tenants t
LEFT JOIN users u ON t.id = u.tenant_id
LEFT JOIN vehicles v ON t.id = v.tenant_id
LEFT JOIN bookings b ON t.id = b.tenant_id
WHERE t.status = 'active'
GROUP BY t.id;

-- View: Tenant admin access
CREATE OR REPLACE VIEW v_tenant_admin_access AS
SELECT 
    ta.id as admin_id,
    ta.username,
    ta.email,
    ta.fullname,
    ta.is_super_admin,
    t.id as tenant_id,
    t.name as tenant_name,
    t.subdomain,
    taa.role,
    taa.granted_at
FROM tenant_admins ta
INNER JOIN tenant_admin_access taa ON ta.id = taa.tenant_admin_id
INNER JOIN tenants t ON taa.tenant_id = t.id
WHERE t.status = 'active';

-- ============================================================================
-- Success message
-- ============================================================================
SELECT 'Multi-tenant schema created successfully!' AS message;
SELECT CONCAT('Total tenants: ', COUNT(*)) AS tenants_count FROM tenants;
SELECT CONCAT('Total tenant admins: ', COUNT(*)) AS admins_count FROM tenant_admins;
