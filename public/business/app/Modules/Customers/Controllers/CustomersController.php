<?php
/**
 * Customers Module - Controller
 */

namespace App\Modules\Customers\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use App\Modules\Customers\Models\Customer;

class CustomersController extends Controller
{
    /**
     * List all customers
     */
    public function index()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();

        $customers = $db->fetchAll(
            "SELECT * FROM customers WHERE tenant_id = ? ORDER BY created_at DESC",
            [$tenantId]
        );

        return $this->view('Customers.index', [
            'title' => 'Customers',
            'customers' => $customers
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        return $this->view('Customers.create', [
            'title' => 'Add Customer'
        ]);
    }

    /**
     * Store customer
     */
    public function store()
    {
        $validator = new Validator($_POST, [
            'name' => 'required|min:3',
            'email' => 'email',
            'phone' => 'required',
        ]);

        if (!$validator->validate()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $customer = new Customer();
        $customer->fill($_POST);
        $customer->save();

        return $this->json(['success' => true, 'message' => 'Customer added successfully', 'id' => $customer->id]);
    }

    /**
     * Show customer
     */
    public function show($id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return $this->json(['error' => 'Customer not found'], 404);
        }

        return $this->view('Customers.show', [
            'title' => 'Customer Details',
            'customer' => $customer->toArray()
        ]);
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return $this->json(['error' => 'Customer not found'], 404);
        }

        return $this->view('Customers.edit', [
            'title' => 'Edit Customer',
            'customer' => $customer->toArray()
        ]);
    }

    /**
     * Update customer
     */
    public function update($id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return $this->json(['error' => 'Customer not found'], 404);
        }

        $customer->fill($_POST);
        $customer->save();

        return $this->json(['success' => true, 'message' => 'Customer updated successfully']);
    }

    /**
     * Delete customer
     */
    public function destroy($id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return $this->json(['error' => 'Customer not found'], 404);
        }

        $customer->delete();

        return $this->json(['success' => true, 'message' => 'Customer deleted successfully']);
    }
}
