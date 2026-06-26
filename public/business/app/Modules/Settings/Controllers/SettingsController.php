<?php
/**
 * Settings Module - Controller
 */

namespace App\Modules\Settings\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;

class SettingsController extends Controller
{
    /**
     * Settings index
     */
    public function index()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();

        $tenant = $db->fetch(
            "SELECT * FROM tenants WHERE id = ?",
            [$tenantId]
        );

        return $this->view('Settings.index', [
            'title' => 'Settings',
            'tenant' => $tenant
        ]);
    }

    /**
     * Update settings
     */
    public function update()
    {
        $db = Database::getInstance();
        $tenantId = $this->tenantId();

        $validator = new Validator($_POST, [
            'company_name' => 'required|min:3',
            'currency' => 'required',
        ]);

        if (!$validator->validate()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $db->update('tenants', [
            'company_name' => $_POST['company_name'],
            'currency' => $_POST['currency'],
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'address' => $_POST['address'] ?? '',
        ], ['id' => $tenantId]);

        return $this->json(['success' => true, 'message' => 'Settings updated successfully']);
    }

    /**
     * List users
     */
    public function users()
    {
        $db = Database::getInstance();
        $tenantId = $this->tenantId();

        $users = $db->fetchAll(
            "SELECT id, name, email, role, is_active FROM users WHERE tenant_id = ? ORDER BY name",
            [$tenantId]
        );

        return $this->view('Settings.users', [
            'title' => 'Users',
            'users' => $users
        ]);
    }

    /**
     * Create user
     */
    public function createUser()
    {
        $validator = new Validator($_POST, [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'role' => 'required',
            'password' => 'required|min:6'
        ]);

        if (!$validator->validate()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $db = Database::getInstance();
        $tenantId = $this->tenantId();

        // Check if email exists
        if ($db->fetch(
            "SELECT id FROM users WHERE email = ? AND tenant_id = ?",
            [$_POST['email'], $tenantId]
        )) {
            return $this->json(['success' => false, 'message' => 'Email already exists'], 409);
        }

        $userId = $db->insert('users', [
            'tenant_id' => $tenantId,
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            'role' => $_POST['role'],
            'is_active' => 1
        ]);

        return $this->json([
            'success' => true,
            'message' => 'User created successfully',
            'id' => $userId
        ]);
    }
}
