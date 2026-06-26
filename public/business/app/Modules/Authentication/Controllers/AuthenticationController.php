<?php
/**
 * Authentication Module - Controller
 */

namespace App\Modules\Authentication\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Tenant;
use App\Core\Database;
use App\Core\Validator;

class AuthenticationController extends Controller
{
    /**
     * Show login page
     */
    public function login()
    {
        if (Auth::getInstance()->check()) {
            redirect('/business/dashboard');
        }
        
        return $this->view('Authentication.login', [
            'title' => 'Login - ONYX Accounting',
            'layout' => false
        ]);
    }

    /**
     * Handle login
     */
    public function handleLogin()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $tenantSlug = $_POST['tenant_slug'] ?? '';

        // Validate input
        $validator = new Validator($_POST, [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'tenant_slug' => 'required'
        ]);

        if (!$validator->validate()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Get tenant
        $tenant = Tenant::findBySlug($tenantSlug);
        if (!$tenant) {
            return $this->json(['success' => false, 'message' => 'Invalid company'], 401);
        }

        // Authenticate
        $auth = Auth::getInstance();
        if ($auth->login($email, $password, $tenant['id'])) {
            return $this->json(['success' => true, 'redirect' => '/business/dashboard']);
        }

        return $this->json(['success' => false, 'message' => 'Invalid credentials'], 401);
    }

    /**
     * Show registration page
     */
    public function register()
    {
        return $this->view('Authentication.register', [
            'title' => 'Register - ONYX Accounting',
            'layout' => false
        ]);
    }

    /**
     * Handle registration
     */
    public function handleRegister()
    {
        $companyName = $_POST['company_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirmation'] ?? '';
        $currency = $_POST['currency'] ?? 'UGX';

        // Validate
        $validator = new Validator($_POST, [
            'company_name' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required'
        ]);

        if (!$validator->validate()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if ($password !== $passwordConfirm) {
            return $this->json(['success' => false, 'message' => 'Passwords do not match'], 422);
        }

        // Create slug
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $companyName));
        $slug = trim($slug, '-');

        // Check if company exists
        $db = Database::getInstance();
        if ($db->fetch("SELECT id FROM tenants WHERE slug = ?", [$slug])) {
            return $this->json(['success' => false, 'message' => 'Company already exists'], 409);
        }

        try {
            $db->beginTransaction();

            // Create tenant
            $tenantId = $db->insert('tenants', [
                'company_name' => $companyName,
                'slug' => $slug,
                'currency' => $currency,
                'fiscal_year_start' => date('Y-01-01'),
                'status' => 'trial',
            ]);

            // Create admin user
            $db->insert('users', [
                'tenant_id' => $tenantId,
                'name' => 'Administrator',
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'role' => 'company_admin',
                'is_active' => 1,
            ]);

            $db->commit();

            return $this->json([
                'success' => true,
                'message' => 'Company registered successfully',
                'redirect' => '/business/auth/login'
            ]);
        } catch (\Exception $e) {
            $db->rollback();
            error_log('Registration failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Registration failed'], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        Auth::getInstance()->logout();
        redirect('/business/auth/login');
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        $user = $this->user();
        return $this->view('Authentication.profile', [
            'user' => $user,
            'title' => 'My Profile'
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile()
    {
        $user = $this->user();
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $validator = new Validator($_POST, [
            'name' => 'required|min:3',
            'email' => 'required|email',
        ]);

        if (!$validator->validate()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $db = Database::getInstance();
        $data = ['name' => $name, 'email' => $email];

        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $db->update('users', $data, ['id' => $user['id']]);

        return $this->json(['success' => true, 'message' => 'Profile updated successfully']);
    }
}
