<?php
/**
 * Reports Module - Controller
 */

namespace App\Modules\Reports\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ReportsController extends Controller
{
    /**
     * Reports index
     */
    public function index()
    {
        return $this->view('Reports.index', [
            'title' => 'Reports'
        ]);
    }

    /**
     * Sales report
     */
    public function salesReport()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $sales = $db->fetchAll(
            "SELECT DATE(invoice_date) as date, SUM(total_amount) as total, COUNT(*) as count 
             FROM invoices WHERE tenant_id = ? AND invoice_date BETWEEN ? AND ? AND status != 'cancelled' 
             GROUP BY DATE(invoice_date) ORDER BY date DESC",
            [$tenantId, $startDate, $endDate]
        );

        return $this->view('Reports.sales', [
            'title' => 'Sales Report',
            'sales' => $sales,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    /**
     * Expenses report
     */
    public function expensesReport()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $expenses = $db->fetchAll(
            "SELECT ec.name as category, SUM(e.amount) as total, COUNT(*) as count 
             FROM expenses e 
             LEFT JOIN expense_categories ec ON e.expense_category_id = ec.id 
             WHERE e.tenant_id = ? AND e.expense_date BETWEEN ? AND ? 
             GROUP BY ec.id ORDER BY total DESC",
            [$tenantId, $startDate, $endDate]
        );

        return $this->view('Reports.expenses', [
            'title' => 'Expenses Report',
            'expenses' => $expenses,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    /**
     * Inventory report
     */
    public function inventoryReport()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();

        $inventory = $db->fetchAll(
            "SELECT p.sku, p.name, p.current_stock, p.cost_price, (p.current_stock * p.cost_price) as value 
             FROM products p 
             WHERE p.tenant_id = ? ORDER BY p.name",
            [$tenantId]
        );

        return $this->view('Reports.inventory', [
            'title' => 'Inventory Report',
            'inventory' => $inventory
        ]);
    }

    /**
     * Customers report
     */
    public function customersReport()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();

        $customers = $db->fetchAll(
            "SELECT c.id, c.name, COUNT(i.id) as invoice_count, SUM(i.total_amount) as total_sales 
             FROM customers c 
             LEFT JOIN invoices i ON c.id = i.customer_id AND i.tenant_id = ? 
             WHERE c.tenant_id = ? 
             GROUP BY c.id ORDER BY total_sales DESC",
            [$tenantId, $tenantId]
        );

        return $this->view('Reports.customers', [
            'title' => 'Customers Report',
            'customers' => $customers
        ]);
    }

    /**
     * Download PDF
     */
    public function downloadPdf($id)
    {
        // TODO: Implement PDF generation
        return $this->json(['message' => 'PDF generation coming soon']);
    }
}
