<?php
/**
 * ONYX Accounting System - Session Class
 * Manages user sessions
 */

namespace App\Core;

class Session
{
    private static $instance;

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
        $this->startSession();
    }

    /**
     * Start session
     */
    private function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'cookie_samesite' => 'Strict',
            ]);
        }
    }

    /**
     * Set session value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
        return $this;
    }

    /**
     * Get session value
     */
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if key exists
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session value
     */
    public function forget($key)
    {
        unset($_SESSION[$key]);
        return $this;
    }

    /**
     * Clear all session data
     */
    public function flush()
    {
        session_destroy();
        return $this;
    }

    /**
     * Flash a message
     */
    public function flash($key, $message)
    {
        $_SESSION['_flash'][$key] = $message;
        return $this;
    }

    /**
     * Get flash message
     */
    public function getFlash($key, $default = null)
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}
