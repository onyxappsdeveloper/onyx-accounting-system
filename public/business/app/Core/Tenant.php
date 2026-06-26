<?php
/**
 * ONYX Accounting System - Tenant Class
 * Manages multi-tenant functionality
 */

namespace App\Core;

class Tenant
{
    private static $instance;
    private $current;
    private $tenantId;

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * Set instance
     */
    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loadFromSession();
    }

    /**
     * Load tenant from session
     */
    private function loadFromSession()
    {
        if (isset($_SESSION['tenant_id'])) {
            $this->tenantId = $_SESSION['tenant_id'];
            $this->loadTenant();
        }
    }

    /**
     * Load tenant from database
     */
    private function loadTenant()
    {
        if ($this->tenantId) {
            $db = Database::getInstance();
            if ($db) {
                $this->current = $db->fetch(
                    "SELECT * FROM tenants WHERE id = ? AND status IN ('active', 'trial')",
                    [$this->tenantId]
                );
            }
        }
    }

    /**
     * Get current tenant
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * Get tenant ID
     */
    public function id()
    {
        return $this->tenantId;
    }

    /**
     * Get tenant by ID
     */
    public static function find($id)
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM tenants WHERE id = ? AND status IN ('active', 'trial')",
            [$id]
        );
    }

    /**
     * Get tenant by slug
     */
    public static function findBySlug($slug)
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM tenants WHERE slug = ? AND status IN ('active', 'trial')",
            [$slug]
        );
    }

    /**
     * Set current tenant
     */
    public function set($tenantId)
    {
        $this->tenantId = $tenantId;
        $_SESSION['tenant_id'] = $tenantId;
        $this->loadTenant();
        return $this;
    }

    /**
     * Get tenant info
     */
    public function info($key = null)
    {
        if ($key === null) {
            return $this->current;
        }
        return $this->current[$key] ?? null;
    }
}
