<?php
/**
 * Suppliers Module - Controller
 */

namespace App\Modules\Suppliers\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use App\Modules\Suppliers\Models\Supplier;

class SuppliersController extends Controller
{
    /**
     * List all suppliers
     */
    public function index()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();

        $suppliers = $db->fetchAll(
            "SELECT * FROM suppliers WHERE tenant_id = ? ORDER BY created_at DESC",
            [$tenantId]
        );

        return $this->view('Suppliers.index', [
            'title' => 'Suppliers',
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        return $this->view('Suppliers.create', [
            'title' => 'Add Supplier'
        ]);
    }

    /**
     * Store supplier
     */
    public function store()
    {
        $validator = new Validator($_POST, [
            'company_name' => 'required|min:3',
            'email' => 'email',
            'phone' => 'required',
        ]);

        if (!$validator->validate()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $supplier = new Supplier();
        $supplier->fill($_POST);
        $supplier->save();

        return $this->json(['success' => true, 'message' => 'Supplier added successfully', 'id' => $supplier->id]);
    }

    /**
     * Show supplier
     */
    public function show($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return $this->json(['error' => 'Supplier not found'], 404);
        }

        return $this->view('Suppliers.show', [
            'title' => 'Supplier Details',
            'supplier' => $supplier->toArray()
        ]);
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return $this->json(['error' => 'Supplier not found'], 404);
        }

        return $this->view('Suppliers.edit', [
            'title' => 'Edit Supplier',
            'supplier' => $supplier->toArray()
        ]);
    }

    /**
     * Update supplier
     */
    public function update($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return $this->json(['error' => 'Supplier not found'], 404);
        }

        $supplier->fill($_POST);
        $supplier->save();

        return $this->json(['success' => true, 'message' => 'Supplier updated successfully']);
    }

    /**
     * Delete supplier
     */
    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return $this->json(['error' => 'Supplier not found'], 404);
        }

        $supplier->delete();

        return $this->json(['success' => true, 'message' => 'Supplier deleted successfully']);
    }
}
