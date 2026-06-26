<?php
/**
 * Expenses Module - Controller
 */

namespace App\Modules\Expenses\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;

class ExpensesController extends Controller
{
    /**
     * List expenses
     */
    public function index()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();
        $page = $_GET['page'] ?? 1;
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $expenses = $db->fetchAll(
            "SELECT e.*, ec.name as category_name FROM expenses e 
             LEFT JOIN expense_categories ec ON e.expense_category_id = ec.id 
             WHERE e.tenant_id = ? ORDER BY e.created_at DESC LIMIT ? OFFSET ?",
            [$tenantId, $perPage, $offset]
        );

        $total = $db->fetch(
            "SELECT COUNT(*) as count FROM expenses WHERE tenant_id = ?",
            [$tenantId]
        )['count'];

        return $this->view('Expenses.index', [
            'title' => 'Expenses',
            'expenses' => $expenses,
            'total' => $total,
            'page' => $page
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
            "SELECT id, name FROM expense_categories WHERE tenant_id = ? ORDER BY name",
            [$tenantId]
        );

        return $this->view('Expenses.create', [
            'title' => 'Record Expense',
            'categories' => $categories
        ]);
    }

    /**
     * Store expense
     */
    public function store()
    {
        $validator = new Validator($_POST, [
            'expense_category_id' => 'required|numeric',
            'amount' => 'required|numeric',
            'expense_date' => 'required',
        ]);

        if (!$validator->validate()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $db = Database::getInstance();
        $tenantId = $this->tenantId();
        $userId = $this->user()['id'];

        // Generate expense number
        $expenseNumber = $this->generateExpenseNumber($tenantId);

        $expenseId = $db->insert('expenses', [
            'tenant_id' => $tenantId,
            'expense_category_id' => $_POST['expense_category_id'],
            'expense_number' => $expenseNumber,
            'expense_date' => $_POST['expense_date'],
            'vendor_name' => $_POST['vendor_name'] ?? '',
            'amount' => $_POST['amount'],
            'payment_method' => $_POST['payment_method'] ?? 'cash',
            'description' => $_POST['description'] ?? '',
            'status' => 'approved',
            'created_by' => $userId
        ]);

        return $this->json([
            'success' => true,
            'message' => 'Expense recorded successfully',
            'id' => $expenseId
        ]);
    }

    /**
     * Show expense
     */
    public function show($id)
    {
        $expense = $this->getExpense($id);
        if (!$expense) {
            return $this->json(['error' => 'Expense not found'], 404);
        }

        return $this->view('Expenses.show', [
            'title' => 'Expense #' . $expense['expense_number'],
            'expense' => $expense
        ]);
    }

    /**
     * Update expense
     */
    public function update($id)
    {
        $expense = $this->getExpense($id);
        if (!$expense) {
            return $this->json(['error' => 'Expense not found'], 404);
        }

        $db = Database::getInstance();
        $db->update('expenses', $_POST, ['id' => $id, 'tenant_id' => $this->tenantId()]);

        return $this->json(['success' => true, 'message' => 'Expense updated successfully']);
    }

    /**
     * Delete expense
     */
    public function destroy($id)
    {
        $expense = $this->getExpense($id);
        if (!$expense) {
            return $this->json(['error' => 'Expense not found'], 404);
        }

        $db = Database::getInstance();
        $db->delete('expenses', ['id' => $id, 'tenant_id' => $this->tenantId()]);

        return $this->json(['success' => true, 'message' => 'Expense deleted successfully']);
    }

    /**
     * Get expense
     */
    private function getExpense($id)
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM expenses WHERE id = ? AND tenant_id = ?",
            [$id, $this->tenantId()]
        );
    }

    /**
     * Generate expense number
     */
    private function generateExpenseNumber($tenantId)
    {
        $db = Database::getInstance();
        $sequence = $db->fetch(
            "SELECT next_number FROM number_sequences WHERE tenant_id = ? AND sequence_type = 'expense'",
            [$tenantId]
        );

        if ($sequence) {
            $nextNumber = $sequence['next_number'];
            $db->update(
                'number_sequences',
                ['next_number' => $nextNumber + 1],
                ['tenant_id' => $tenantId, 'sequence_type' => 'expense']
            );
        } else {
            $nextNumber = 5001;
            $db->insert('number_sequences', [
                'tenant_id' => $tenantId,
                'sequence_type' => 'expense',
                'prefix' => 'EXP',
                'next_number' => $nextNumber + 1
            ]);
        }

        return 'EXP-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
