<?php
/**
 * TenantManager - Multi-tenant context manager
 * Handles tenant identification, switching, and data isolation
 */

class TenantManager {
    private static $instance = null;
    private $currentTenant = null;
    private $currentTenantAdmin = null;
    private $db = null;

    private function __construct() {
        require_once __DIR__ . '/DatabaseMariaDB.php';
        $this->db = DatabaseMariaDB::getConnection();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Identify tenant from subdomain, domain, or explicit parameter
     * Priority: 1) Explicit tenant_id, 2) Subdomain, 3) Domain, 4) Session
     */
    public function identifyTenant($tenantIdentifier = null) {
        // If tenant already identified in this request
        if ($this->currentTenant !== null) {
            return $this->currentTenant;
        }

        // Check session first
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Explicit tenant ID (for API calls, admin panel)
        if ($tenantIdentifier !== null) {
            return $this->loadTenantById($tenantIdentifier);
        }

        // 2. Check session
        if (isset($_SESSION['tenant_id'])) {
            return $this->loadTenantById($_SESSION['tenant_id']);
        }

        // 3. Subdomain detection
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $parts = explode('.', $host);
        
        // If subdomain exists (e.g., demo.eazyride.com)
        if (count($parts) >= 3) {
            $subdomain = $parts[0];
            $tenant = $this->loadTenantBySubdomain($subdomain);
            if ($tenant) {
                return $tenant;
            }
        }

        // 4. Full domain detection
        $tenant = $this->loadTenantByDomain($host);
        if ($tenant) {
            return $tenant;
        }

        // 5. GET/POST parameter (for development/testing)
        if (isset($_GET['tenant_id'])) {
            return $this->loadTenantById($_GET['tenant_id']);
        }
        if (isset($_POST['tenant_id'])) {
            return $this->loadTenantById($_POST['tenant_id']);
        }

        // Default: return null (no tenant identified)
        return null;
    }

    /**
     * Load tenant by ID
     */
    private function loadTenantById($tenantId) {
        $stmt = $this->db->prepare("
            SELECT * FROM tenants 
            WHERE id = ? AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$tenantId]);
        
        if ($tenant = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->currentTenant = $tenant;
            $_SESSION['tenant_id'] = $tenant['id'];
            return $tenant;
        }
        
        return null;
    }

    /**
     * Load tenant by subdomain
     */
    private function loadTenantBySubdomain($subdomain) {
        $stmt = $this->db->prepare("
            SELECT * FROM tenants 
            WHERE subdomain = ? AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$subdomain]);
        
        if ($tenant = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->currentTenant = $tenant;
            $_SESSION['tenant_id'] = $tenant['id'];
            return $tenant;
        }
        
        return null;
    }

    /**
     * Load tenant by domain
     */
    private function loadTenantByDomain($domain) {
        $stmt = $this->db->prepare("
            SELECT * FROM tenants 
            WHERE domain = ? AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$domain]);
        
        if ($tenant = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->currentTenant = $tenant;
            $_SESSION['tenant_id'] = $tenant['id'];
            return $tenant;
        }
        
        return null;
    }

    /**
     * Get current tenant
     */
    public function getCurrentTenant() {
        if ($this->currentTenant === null) {
            $this->identifyTenant();
        }
        return $this->currentTenant;
    }

    /**
     * Get current tenant ID
     */
    public function getCurrentTenantId() {
        $tenant = $this->getCurrentTenant();
        return $tenant ? $tenant['id'] : null;
    }

    /**
     * Switch tenant (for super admin)
     */
    public function switchTenant($tenantId) {
        // Verify super admin or has access
        if (!$this->canAccessTenant($tenantId)) {
            throw new Exception("Access denied to tenant");
        }
        
        $this->currentTenant = null;
        return $this->loadTenantById($tenantId);
    }

    /**
     * Check if current admin can access tenant
     */
    private function canAccessTenant($tenantId) {
        // If super admin, allow all
        if ($this->currentTenantAdmin && $this->currentTenantAdmin['is_super_admin']) {
            return true;
        }

        // Check tenant_admin_access table
        if ($this->currentTenantAdmin) {
            $stmt = $this->db->prepare("
                SELECT 1 FROM tenant_admin_access 
                WHERE tenant_admin_id = ? AND tenant_id = ?
                LIMIT 1
            ");
            $adminId = $this->currentTenantAdmin['id'];
            $stmt->execute([$adminId, $tenantId]);
            return $stmt->fetch() !== false;
        }

        return false;
    }

    /**
     * Authenticate tenant admin (super admin panel)
     */
    public function authenticateTenantAdmin($username, $password) {
        $stmt = $this->db->prepare("
            SELECT * FROM tenant_admins 
            WHERE username = ? OR email = ?
            LIMIT 1
        ");
        $stmt->execute([$username, $username]);
        
        if ($admin = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($password, $admin['password'])) {
                $this->currentTenantAdmin = $admin;
                $_SESSION['tenant_admin_id'] = $admin['id'];
                $_SESSION['is_super_admin'] = $admin['is_super_admin'];
                
                // Update last login
                $stmt = $this->db->prepare("
                    UPDATE tenant_admins 
                    SET last_login = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$admin['id']]);
                
                return $admin;
            }
        }
        
        return false;
    }

    /**
     * Get current tenant admin
     */
    public function getCurrentTenantAdmin() {
        if ($this->currentTenantAdmin === null && isset($_SESSION['tenant_admin_id'])) {
            $stmt = $this->db->prepare("
                SELECT * FROM tenant_admins WHERE id = ? LIMIT 1
            ");
            $adminId = $_SESSION['tenant_admin_id'];
            $stmt->execute([$adminId]);
            $this->currentTenantAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $this->currentTenantAdmin;
    }

    /**
     * Check if current user is super admin
     */
    public function isSuperAdmin() {
        $admin = $this->getCurrentTenantAdmin();
        return $admin && $admin['is_super_admin'];
    }

    /**
     * Get all tenants (super admin only)
     */
    public function getAllTenants($status = 'active') {
        if (!$this->isSuperAdmin()) {
            throw new Exception("Super admin access required");
        }

        $query = "SELECT * FROM tenants";
        if ($status !== 'all') {
            $query .= " WHERE status = ?";
        }
        $query .= " ORDER BY created_at DESC";

        if ($status !== 'all') {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$status]);
        } else {
            $stmt = $this->db->query($query);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get tenants accessible by current admin
     */
    public function getAccessibleTenants() {
        $admin = $this->getCurrentTenantAdmin();
        if (!$admin) {
            return [];
        }

        if ($admin['is_super_admin']) {
            return $this->getAllTenants('all');
        }

        $stmt = $this->db->prepare("
            SELECT t.*, taa.role 
            FROM tenants t
            INNER JOIN tenant_admin_access taa ON t.id = taa.tenant_id
            WHERE taa.tenant_admin_id = ? AND t.status = 'active'
            ORDER BY t.name
        ");
        $adminId = $admin['id'];
        $stmt->execute([$adminId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new tenant
     */
    public function createTenant($data) {
        if (!$this->isSuperAdmin()) {
            throw new Exception("Super admin access required");
        }

        $stmt = $this->db->prepare("
            INSERT INTO tenants (
                name, subdomain, domain, contact_email, contact_phone,
                city, country, subscription_plan, subscription_start,
                subscription_end, status, settings
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $settings = isset($data['settings']) ? json_encode($data['settings']) : '{}';
        
        if ($stmt->execute([
            $data['name'],
            $data['subdomain'],
            $data['domain'],
            $data['contact_email'],
            $data['contact_phone'],
            $data['city'],
            $data['country'],
            $data['subscription_plan'],
            $data['subscription_start'],
            $data['subscription_end'],
            $data['status'],
            $settings
        ])) {
            return $this->db->lastInsertId();
        }

        throw new Exception("Failed to create tenant");
    }

    /**
     * Update tenant
     */
    public function updateTenant($tenantId, $data) {
        if (!$this->canAccessTenant($tenantId)) {
            throw new Exception("Access denied");
        }

        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }

        $values[] = $tenantId;

        $query = "UPDATE tenants SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($query);

        return $stmt->execute($values);
    }

    /**
     * Delete tenant (soft delete - set status to inactive)
     */
    public function deleteTenant($tenantId) {
        if (!$this->isSuperAdmin()) {
            throw new Exception("Super admin access required");
        }

        $stmt = $this->db->prepare("
            UPDATE tenants SET status = 'inactive' WHERE id = ?
        ");
        return $stmt->execute([$tenantId]);
    }

    /**
     * Get tenant statistics
     */
    public function getTenantStats($tenantId) {
        if (!$this->canAccessTenant($tenantId)) {
            throw new Exception("Access denied");
        }

        $stmt = $this->db->prepare("
            SELECT 
                (SELECT COUNT(*) FROM users WHERE tenant_id = ?) as total_users,
                (SELECT COUNT(*) FROM vehicles WHERE tenant_id = ?) as total_vehicles,
                (SELECT COUNT(*) FROM bookings WHERE tenant_id = ?) as total_bookings,
                (SELECT COUNT(*) FROM bookings WHERE tenant_id = ? AND status = 'active') as active_bookings,
                (SELECT SUM(total_cost) FROM bookings WHERE tenant_id = ?) as total_revenue
        ");
        $stmt->execute([$tenantId, $tenantId, $tenantId, $tenantId, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Clear tenant context (logout)
     */
    public function clearTenantContext() {
        $this->currentTenant = null;
        $this->currentTenantAdmin = null;
        unset($_SESSION['tenant_id']);
        unset($_SESSION['tenant_admin_id']);
        unset($_SESSION['is_super_admin']);
    }

    /**
     * Validate tenant limits
     */
    public function validateTenantLimits($tenantId, $type) {
        $tenant = $this->loadTenantById($tenantId);
        if (!$tenant) {
            return false;
        }

        switch ($type) {
            case 'users':
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE tenant_id = ?");
                $stmt->execute([$tenantId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['count'] < $tenant['max_users'];

            case 'vehicles':
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM vehicles WHERE tenant_id = ?");
                $stmt->execute([$tenantId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['count'] < $tenant['max_vehicles'];

            default:
                return true;
        }
    }
}
