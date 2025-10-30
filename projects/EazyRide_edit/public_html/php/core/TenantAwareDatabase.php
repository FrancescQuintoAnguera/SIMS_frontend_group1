<?php
/**
 * TenantAwareDatabase - Database wrapper with automatic tenant filtering
 * Automatically adds tenant_id to all queries for data isolation
 */

require_once __DIR__ . '/TenantManager.php';
require_once __DIR__ . '/DatabaseMariaDB.php';

class TenantAwareDatabase {
    private $db;
    private $tenantManager;
    private $currentTenantId;

    public function __construct() {
        $this->db = DatabaseMariaDB::getConnection();
        $this->tenantManager = TenantManager::getInstance();
        $tenant = $this->tenantManager->getCurrentTenant();
        $this->currentTenantId = $tenant ? $tenant['id'] : null;
    }

    /**
     * Get raw database connection (use carefully!)
     */
    public function getRawConnection() {
        return $this->db;
    }

    /**
     * Get current tenant ID
     */
    public function getTenantId() {
        return $this->currentTenantId;
    }

    /**
     * Execute query with automatic tenant filtering
     */
    public function query($sql, $params = []) {
        // Add tenant_id filter automatically if query contains tenant-aware tables
        $sql = $this->addTenantFilter($sql);
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Query preparation failed");
        }

        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }

        return $stmt;
    }

    /**
     * Insert with automatic tenant_id
     */
    public function insert($table, $data) {
        if ($this->isTenantAwareTable($table)) {
            $data['tenant_id'] = $this->currentTenantId;
        }

        $columns = array_keys($data);
        $placeholders = array_fill(0, count($data), '?');
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Insert preparation failed");
        }

        $values = array_values($data);
        
        if ($stmt->execute($values)) {
            return $this->db->lastInsertId();
        }

        throw new Exception("Insert failed");
    }

    /**
     * Update with automatic tenant filtering
     */
    public function update($table, $data, $where, $whereParams = []) {
        $sets = [];
        $values = [];

        foreach ($data as $column => $value) {
            $sets[] = "$column = ?";
            $values[] = $value;
        }

        $sql = sprintf("UPDATE %s SET %s WHERE %s", $table, implode(', ', $sets), $where);

        // Add tenant filter
        if ($this->isTenantAwareTable($table)) {
            $sql .= " AND tenant_id = ?";
            $values[] = $this->currentTenantId;
        }

        // Add where parameters
        foreach ($whereParams as $param) {
            $values[] = $param;
        }

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Update preparation failed");
        }

        return $stmt->execute($values);
    }

    /**
     * Delete with automatic tenant filtering
     */
    public function delete($table, $where, $whereParams = []) {
        $sql = sprintf("DELETE FROM %s WHERE %s", $table, $where);

        $values = $whereParams;

        // Add tenant filter
        if ($this->isTenantAwareTable($table)) {
            $sql .= " AND tenant_id = ?";
            $values[] = $this->currentTenantId;
        }

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Delete preparation failed");
        }

        return $stmt->execute($values);
    }

    /**
     * Select with automatic tenant filtering
     */
    public function select($table, $columns = '*', $where = '1=1', $whereParams = [], $orderBy = '', $limit = '') {
        $sql = sprintf("SELECT %s FROM %s WHERE %s", 
            is_array($columns) ? implode(', ', $columns) : $columns,
            $table,
            $where
        );

        $values = $whereParams;

        // Add tenant filter
        if ($this->isTenantAwareTable($table)) {
            $sql .= " AND tenant_id = ?";
            $values[] = $this->currentTenantId;
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Select preparation failed");
        }

        if (!empty($values)) {
            $stmt->execute($values);
        } else {
            $stmt->execute();
        }

        return $stmt;
    }

    /**
     * Select one record
     */
    public function selectOne($table, $columns = '*', $where = '1=1', $whereParams = []) {
        $stmt = $this->select($table, $columns, $where, $whereParams, '', 1);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Count records with tenant filter
     */
    public function count($table, $where = '1=1', $whereParams = []) {
        $stmt = $this->select($table, 'COUNT(*) as count', $where, $whereParams);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    /**
     * Check if table is tenant-aware
     */
    private function isTenantAwareTable($table) {
        $tenantTables = [
            'users', 'vehicles', 'locations', 'bookings', 
            'subscriptions', 'vehicle_usage', 'payments'
        ];
        return in_array($table, $tenantTables);
    }

    /**
     * Add tenant filter to SQL queries
     */
    private function addTenantFilter($sql) {
        if (!$this->currentTenantId) {
            return $sql;
        }

        // Simple tenant filtering for common patterns
        // This is a basic implementation - enhance as needed
        $tenantTables = [
            'users', 'vehicles', 'locations', 'bookings', 
            'subscriptions', 'vehicle_usage', 'payments'
        ];

        foreach ($tenantTables as $table) {
            // Add tenant_id to WHERE clause if table exists in query
            if (stripos($sql, "FROM $table") !== false || stripos($sql, "JOIN $table") !== false) {
                // Check if WHERE clause exists
                if (stripos($sql, 'WHERE') !== false) {
                    // Already has WHERE - add AND condition
                    $sql = preg_replace(
                        "/WHERE\s+/i",
                        "WHERE $table.tenant_id = {$this->currentTenantId} AND ",
                        $sql,
                        1
                    );
                } else {
                    // No WHERE clause - add one before ORDER BY, GROUP BY, LIMIT, etc.
                    $sql = preg_replace(
                        "/(ORDER BY|GROUP BY|LIMIT|$)/i",
                        " WHERE $table.tenant_id = {$this->currentTenantId} $1",
                        $sql,
                        1
                    );
                }
            }
        }

        return $sql;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->db->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->db->rollback();
    }

    /**
     * Get last insert ID
     */
    public function getInsertId() {
        return $this->db->lastInsertId();
    }

    /**
     * Get affected rows
     */
    public function getAffectedRows() {
        return $this->db->rowCount();
    }

    /**
     * Escape string (PDO handles this via prepared statements)
     */
    public function escape($string) {
        return $this->db->quote($string);
    }
}
