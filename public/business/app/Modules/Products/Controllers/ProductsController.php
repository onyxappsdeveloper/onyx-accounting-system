<?php
/**
 * Products Module - Controller
 */

namespace App\Modules\Products\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use App\Modules\Products\Models\Product;

class ProductsController extends Controller
{
    /**
     * List all products
     */
    public function index()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();
        $page = $_GET['page'] ?? 1;
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $products = $db->fetchAll(
            "SELECT p.*, pc.name as category_name FROM products p 
             LEFT JOIN product_categories pc ON p.category_id = pc.id 
             WHERE p.tenant_id = ? ORDER BY p.created_at DESC LIMIT ? OFFSET ?",
            [$tenantId, $perPage, $offset]
        );

        $total = $db->fetch(
            "SELECT COUNT(*) as count FROM products WHERE tenant_id = ?",
            [$tenantId]
        )['count'];

        return $this->view('Products.index', [
            'title' => 'Products',
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $db = Database::getInstance();
        $tenantId = $this->tenantId();

        $categories = $db->fetchAll(
            "SELECT id, name FROM product_categories WHERE tenant_id = ? ORDER BY name",
            [$tenantId]
        );

        $suppliers = $db->fetchAll(
            "SELECT id, company_name FROM suppliers WHERE tenant_id = ? ORDER BY company_name",
            [$tenantId]
        );

        return $this->view('Products.create', [
            'title' => 'Add Product',
            'categories' => $categories,
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Store product
     */
    public function store()
    {
        $validator = new Validator($_POST, [
            'sku' => 'required',
            'name' => 'required|min:3',
            'category_id' => 'required|numeric',
            'cost_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
        ]);

        if (!$validator->validate()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $product = new Product();
        $product->fill($_POST);
        $product->save();

        return $this->json(['success' => true, 'message' => 'Product added successfully', 'id' => $product->id]);
    }

    /**
     * Show product
     */
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        return $this->view('Products.show', [
            'title' => 'Product Details',
            'product' => $product->toArray()
        ]);
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $db = Database::getInstance();
        $tenantId = $this->tenantId();

        $categories = $db->fetchAll(
            "SELECT id, name FROM product_categories WHERE tenant_id = ? ORDER BY name",
            [$tenantId]
        );

        return $this->view('Products.edit', [
            'title' => 'Edit Product',
            'product' => $product->toArray(),
            'categories' => $categories
        ]);
    }

    /**
     * Update product
     */
    public function update($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $product->fill($_POST);
        $product->save();

        return $this->json(['success' => true, 'message' => 'Product updated successfully']);
    }

    /**
     * Delete product
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $product->delete();

        return $this->json(['success' => true, 'message' => 'Product deleted successfully']);
    }
}
