<?php
/**
 * ONYX Accounting System - Authentication Class
 * Handles user authentication and authorization
 */

namespace App\Core;

class Auth
{
    private static $instance;
    private $user;
    private $userId;
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
     * Load user from session
     */
    private function loadFromSession()
    {
        if (isset($_SESSION['user_id'])) {
            $this->userId = $_SESSION['user_id'];
            $this->tenantId = $_SESSION['tenant_id'];
            $this->loadUser();
        }
    }

    /**
     * Load user from database
     */
    private function loadUser()
    {
        if ($this->userId) {
            $db = Database::getInstance();
            $this->user = $db->fetch(
                "SELECT * FROM users WHERE id = ? AND tenant_id = ? AND is_active = 1",
                [$this->userId, $this->tenantId]
            );
        }
    }

    /**
     * Authenticate user with email and password
     */
    public function login($email, $password, $tenantId)
    {
        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT * FROM users WHERE email = ? AND tenant_id = ? AND is_active = 1",
            [$email, $tenantId]
        );

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['tenant_id'] = $user['tenant_id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            $this->userId = $user['id'];
            $this->tenantId = $user['tenant_id'];
            $this->user = $user;

            // Log audit
            $this->logAudit('user_login', 'User logged in', $user['id']);

            return true;
        }

        return false;
    }

    /**
     * Logout user
     */
    public function logout()
    {
        if ($this->userId) {
            $this->logAudit('user_logout', 'User logged out', $this->userId);
        }

        session_destroy();
        $this->user = null;
        $this->userId = null;
        $this->tenantId = null;
    }

    /**
     * Check if user is authenticated
     */
    public function check()
    {
        return $this->user !== null;
    }

    /**
     * Get current user
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * Get user ID
     */
    public function id()
    {
        return $this->userId;
    }

    /**
     * Get tenant ID
     */
    public function tenantId()
    {
        return $this->tenantId;
    }

    /**
     * Check if user has role
     */
    public function hasRole($role)
    {
        return $this->user && $this->user['role'] === $role;
    }

    /**
     * Check if user has any of the roles
     */
    public function hasAnyRole($roles)
    {
        if (!$this->user) {
            return false;
        }
        return in_array($this->user['role'], $roles);
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($permission)
    {
        // TODO: Implement permission checking
        return true;
    }

    /**
     * Log audit trail
     */
    private function logAudit($action, $description, $userId)
    {
        try {
            $db = Database::getInstance();
            $db->insert('audit_logs', [
                'tenant_id' => $this->tenantId,
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            error_log('Audit log failed: ' . $e->getMessage());
        }
    }
}
