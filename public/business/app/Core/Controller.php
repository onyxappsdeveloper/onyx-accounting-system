<?php
/**
 * ONYX Accounting System - Base Controller Class
 * All controllers inherit from this class
 */

namespace App\Core;

class Controller
{
    protected $view;
    protected $data = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Check authentication for protected routes
        if (!$this->isPublicRoute()) {
            if (!Auth::getInstance()->check()) {
                redirect('/business/auth/login');
            }
        }
    }

    /**
     * Check if route is public
     */
    protected function isPublicRoute()
    {
        $publicRoutes = ['auth/login', 'auth/register', 'auth/forgot-password', 'auth/verify-email'];
        $uri = $_GET['url'] ?? 'dashboard';
        
        foreach ($publicRoutes as $route) {
            if (strpos($uri, $route) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Render a view
     */
    protected function view($view, $data = [])
    {
        $this->data = array_merge($this->data, $data);
        return new View($view, $this->data);
    }

    /**
     * Return JSON response
     */
    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect
     */
    protected function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    /**
     * Get current user
     */
    protected function user()
    {
        return Auth::getInstance()->user();
    }

    /**
     * Get current tenant
     */
    protected function tenant()
    {
        return Tenant::getInstance()->current();
    }

    /**
     * Get current tenant ID
     */
    protected function tenantId()
    {
        return Tenant::getInstance()->id();
    }

    /**
     * Validate input
     */
    protected function validate($data, $rules)
    {
        return Validator::validate($data, $rules);
    }
}
