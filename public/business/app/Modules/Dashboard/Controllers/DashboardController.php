<?php
/**
 * Dashboard Module - Controller
 */

namespace App\Modules\Dashboard\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index()
    {
        $tenantId = $this->tenantId();
        $user = $this->user();
        $db = Database::getInstance();

        $today = date('Y-m-d');
        $thisMonth = date('Y-m-01');

        // Get dashboard metrics
        $metrics = [
            'todaysSales' => $db->fetch(
                "SELECT COALESCE(SUM(total_amount), 0) as total FROM invoices 
                 WHERE tenant_id = ? AND DATE(invoice_date) = ? AND status != 'cancelled'",
                [$tenantId, $today]
            )['total'] ?? 0,

            'monthlySales' => $db->fetch(
                "SELECT COALESCE(SUM(total_amount), 0) as total FROM invoices 
                 WHERE tenant_id = ? AND invoice_date >= ? AND status != 'cancelled'",
                [$tenantId, $thisMonth]
            )['total'] ?? 0,

            'todaysExpenses' => $db->fetch(
                "SELECT COALESCE(SUM(amount), 0) as total FROM expenses 
                 WHERE tenant_id = ? AND DATE(expense_date) = ? AND status = 'paid'",
                [$tenantId, $today]
            )['total'] ?? 0,

            'outstanding' => $db->fetch(
                "SELECT COALESCE(SUM(total_amount - paid_amount), 0) as total FROM invoices 
                 WHERE tenant_id = ? AND status IN ('partial', 'overdue')",
                [$tenantId]
            )['total'] ?? 0,
        ];

        // Calculate profit
        $metrics['todaysProfit'] = $metrics['todaysSales'] - $metrics['todaysExpenses'];
        $metrics['monthlyProfit'] = $db->fetch(
            "SELECT COALESCE(SUM(ii.line_total), 0) - COALESCE((SELECT SUM(amount) FROM expenses WHERE tenant_id = ? AND expense_date >= ?), 0) as total 
             FROM invoice_items ii 
             JOIN invoices i ON ii.invoice_id = i.id 
             WHERE i.tenant_id = ? AND i.invoice_date >= ?",
            [$tenantId, $thisMonth, $tenantId, $thisMonth]
        )['total'] ?? 0;

        // Get recent transactions
        $recentInvoices = $db->fetchAll(
            "SELECT i.*, c.name as customer_name FROM invoices i 
             LEFT JOIN customers c ON i.customer_id = c.id 
             WHERE i.tenant_id = ? ORDER BY i.created_at DESC LIMIT 5",
            [$tenantId]
        );

        return $this->view('Dashboard.index', [
            'title' => 'Dashboard',
            'user' => $user,
            'metrics' => $metrics,
            'recentInvoices' => $recentInvoices
        ]);
    }
}
