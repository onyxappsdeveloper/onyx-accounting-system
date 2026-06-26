<?php
/**
 * Global Helpers
 */

/**
 * Redirect to URL
 */
function redirect($url)
{
    header("Location: $url");
    exit;
}

/**
 * Get current authenticated user
 */
function auth()
{
    return \App\Core\Auth::getInstance();
}

/**
 * Get current tenant
 */
function tenant()
{
    return \App\Core\Tenant::getInstance();
}

/**
 * Get session
 */
function session()
{
    return \App\Core\Session::getInstance();
}

/**
 * Get database instance
 */
function db()
{
    return \App\Core\Database::getInstance();
}

/**
 * Get application instance
 */
function app()
{
    return \App\Core\App::getInstance();
}

/**
 * Escape HTML output
 */
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'UGX')
{
    return $currency . ' ' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'Y-m-d H:i:s')
{
    return date($format, strtotime($date));
}

/**
 * Check if user has permission
 */
function can($permission)
{
    return auth()->hasPermission($permission);
}

/**
 * Check if user has role
 */
function hasRole($role)
{
    return auth()->hasRole($role);
}

/**
 * Get config value
 */
function config($key, $default = null)
{
    return app()->config($key, $default);
}

/**
 * Flash message
 */
function flash($key, $message = null)
{
    if ($message === null) {
        return session()->getFlash($key);
    }
    return session()->flash($key, $message);
}

/**
 * Generate CSRF token
 */
function csrf()
{
    if (!session()->has('_csrf_token')) {
        session()->set('_csrf_token', bin2hex(random_bytes(32)));
    }
    return session()->get('_csrf_token');
}

/**
 * Verify CSRF token
 */
function verifyCsrf($token)
{
    return hash_equals(session()->get('_csrf_token', ''), $token);
}
