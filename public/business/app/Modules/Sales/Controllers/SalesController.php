<?php
/**
 * Sales Module - Controller
 */

namespace App\Modules\Sales\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;

class SalesController extends Controller
{
    /**
     * List invoices
     */
    public function invoices()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();
        $page = $_GET['page'] ?? 1;
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $invoices = $db->fetchAll(
            "SELECT i.*, c.name as customer_name FROM invoices i 
             LEFT JOIN customers c ON i.customer_id = c.id 
             WHERE i.tenant_id = ? ORDER BY i.created_at DESC LIMIT ? OFFSET ?",
            [$tenantId, $perPage, $offset]
        );

        $total = $db->fetch(
            "SELECT COUNT(*) as count FROM invoices WHERE tenant_id = ?",
            [$tenantId]
        )['count'];

        return $this->view('Sales.invoices', [
            'title' => 'Invoices',
            'invoices' => $invoices,
            'total' => $total,
            'page' => $page
        ]);
    }

    /**
     * Show create invoice form
     */
    public function createInvoice()
    {
        $db = Database::getInstance();
        $tenantId = $this->tenantId();

        $customers = $db->fetchAll(
            "SELECT id, name FROM customers WHERE tenant_id = ? ORDER BY name",
            [$tenantId]
        );

        $products = $db->fetchAll(
            "SELECT id, name, selling_price FROM products WHERE tenant_id = ? AND is_active = 1 ORDER BY name",
            [$tenantId]
        );

        return $this->view('Sales.createInvoice', [
            'title' => 'Create Invoice',
            'customers' => $customers,
            'products' => $products
        ]);
    }

    /**
     * Store invoice
     */
    public function storeInvoice()
    {
        $validator = new Validator($_POST, [
            'customer_id' => 'required|numeric',
            'invoice_date' => 'required',
            'items' => 'required'
        ]);

        if (!$validator->validate()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $db = Database::getInstance();
        $tenantId = $this->tenantId();
        $userId = $this->user()['id'];

        try {
            $db->beginTransaction();

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber($tenantId);

            // Insert invoice
            $invoiceId = $db->insert('invoices', [
                'tenant_id' => $tenantId,
                'customer_id' => $_POST['customer_id'],
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $_POST['invoice_date'],
                'due_date' => $_POST['due_date'] ?? null,
                'subtotal' => $_POST['subtotal'] ?? 0,
                'discount_amount' => $_POST['discount_amount'] ?? 0,
                'tax_amount' => $_POST['tax_amount'] ?? 0,
                'total_amount' => $_POST['total_amount'] ?? 0,
                'status' => 'draft',
                'created_by' => $userId
            ]);

            // Insert invoice items
            $items = json_decode($_POST['items'], true);
            foreach ($items as $item) {
                $db->insert('invoice_items', [
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0
                ]);
            }

            $db->commit();

            return $this->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'id' => $invoiceId,
                'redirect' => "/business/invoices/$invoiceId"
            ]);
        } catch (\Exception $e) {
            $db->rollback();
            error_log('Invoice creation failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to create invoice'], 500);
        }
    }

    /**
     * Show invoice
     */
    public function showInvoice($id)
    {
        $db = Database::getInstance();
        $invoice = $db->fetch(
            "SELECT i.*, c.name as customer_name, c.email, c.phone FROM invoices i 
             LEFT JOIN customers c ON i.customer_id = c.id 
             WHERE i.id = ? AND i.tenant_id = ?",
            [$id, $this->tenantId()]
        );

        if (!$invoice) {
            return $this->json(['error' => 'Invoice not found'], 404);
        }

        $items = $db->fetchAll(
            "SELECT ii.*, p.name as product_name FROM invoice_items ii 
             LEFT JOIN products p ON ii.product_id = p.id 
             WHERE ii.invoice_id = ?",
            [$id]
        );

        return $this->view('Sales.showInvoice', [
            'title' => 'Invoice #' . $invoice['invoice_number'],
            'invoice' => $invoice,
            'items' => $items
        ]);
    }

    /**
     * List quotations
     */
    public function quotations()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();

        $quotations = $db->fetchAll(
            "SELECT q.*, c.name as customer_name FROM quotations q 
             LEFT JOIN customers c ON q.customer_id = c.id 
             WHERE q.tenant_id = ? ORDER BY q.created_at DESC",
            [$tenantId]
        );

        return $this->view('Sales.quotations', [
            'title' => 'Quotations',
            'quotations' => $quotations
        ]);
    }

    /**
     * List cash sales
     */
    public function cashSales()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();

        $sales = $db->fetchAll(
            "SELECT * FROM cash_sales WHERE tenant_id = ? ORDER BY created_at DESC",
            [$tenantId]
        );

        return $this->view('Sales.cashSales', [
            'title' => 'Cash Sales',
            'sales' => $sales
        ]);
    }

    /**
     * List receipts
     */
    public function receipts()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();

        $receipts = $db->fetchAll(
            "SELECT r.*, c.name as customer_name FROM receipts r 
             LEFT JOIN customers c ON r.customer_id = c.id 
             WHERE r.tenant_id = ? ORDER BY r.created_at DESC",
            [$tenantId]
        );

        return $this->view('Sales.receipts', [
            'title' => 'Receipts',
            'receipts' => $receipts
        ]);
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoicePdf($id)
    {
        $db = Database::getInstance();
        $invoice = $db->fetch(
            "SELECT i.*, c.name as customer_name FROM invoices i 
             LEFT JOIN customers c ON i.customer_id = c.id 
             WHERE i.id = ? AND i.tenant_id = ?",
            [$id, $this->tenantId()]
        );

        if (!$invoice) {
            return $this->json(['error' => 'Invoice not found'], 404);
        }

        // TODO: Implement PDF generation
        return $this->json(['message' => 'PDF generation coming soon']);
    }

    /**
     * Generate invoice number
     */
    private function generateInvoiceNumber($tenantId)
    {
        $db = Database::getInstance();
        $sequence = $db->fetch(
            "SELECT next_number FROM number_sequences WHERE tenant_id = ? AND sequence_type = 'invoice'",
            [$tenantId]
        );

        if ($sequence) {
            $nextNumber = $sequence['next_number'];
            $db->update(
                'number_sequences',
                ['next_number' => $nextNumber + 1],
                ['tenant_id' => $tenantId, 'sequence_type' => 'invoice']
            );
        } else {
            $nextNumber = 1001;
            $db->insert('number_sequences', [
                'tenant_id' => $tenantId,
                'sequence_type' => 'invoice',
                'prefix' => 'INV',
                'next_number' => $nextNumber + 1
            ]);
        }

        return 'INV-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Edit invoice
     */
    public function editInvoice($id)
    {
        // TODO: Implement
    }

    /**
     * Update invoice
     */
    public function updateInvoice($id)
    {
        // TODO: Implement
    }

    /**
     * Destroy invoice
     */
    public function destroyInvoice($id)
    {
        // TODO: Implement
    }

    /**
     * Create quotation
     */
    public function createQuotation()
    {
        // TODO: Implement
    }

    /**
     * Store quotation
     */
    public function storeQuotation()
    {
        // TODO: Implement
    }

    /**
     * Show quotation
     */
    public function showQuotation($id)
    {
        // TODO: Implement
    }

    /**
     * Update quotation
     */
    public function updateQuotation($id)
    {
        // TODO: Implement
    }

    /**
     * Destroy quotation
     */
    public function destroyQuotation($id)
    {
        // TODO: Implement
    }

    /**
     * Create cash sale
     */
    public function createCashSale()
    {
        // TODO: Implement
    }

    /**
     * Store cash sale
     */
    public function storeCashSale()
    {
        // TODO: Implement
    }

    /**
     * Show cash sale
     */
    public function showCashSale($id)
    {
        // TODO: Implement
    }

    /**
     * Show receipt
     */
    public function showReceipt($id)
    {
        // TODO: Implement
    }

    /**
     * Download receipt PDF
     */
    public function downloadReceiptPdf($id)
    {
        // TODO: Implement
    }
}
