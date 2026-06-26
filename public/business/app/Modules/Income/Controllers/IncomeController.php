<?php
/**
 * Income Module - Controller
 */

namespace App\Modules\Income\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;

class IncomeController extends Controller
{
    /**
     * List income
     */
    public function index()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();
        $page = $_GET['page'] ?? 1;
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $income = $db->fetchAll(
            "SELECT i.*, ic.name as category_name FROM income i 
             LEFT JOIN income_categories ic ON i.income_category_id = ic.id 
             WHERE i.tenant_id = ? ORDER BY i.created_at DESC LIMIT ? OFFSET ?",
            [$tenantId, $perPage, $offset]
        );

        $total = $db->fetch(
            "SELECT COUNT(*) as count FROM income WHERE tenant_id = ?",
            [$tenantId]
        )['count'];

        return $this->view('Income.index', [
            'title' => 'Income',
            'income' => $income,
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
            "SELECT id, name FROM income_categories WHERE tenant_id = ? ORDER BY name",
            [$tenantId]
        );

        return $this->view('Income.create', [
            'title' => 'Record Income',
            'categories' => $categories
        ]);
    }

    /**
     * Store income
     */
    public function store()
    {
        $validator = new Validator($_POST, [
            'income_category_id' => 'required|numeric',
            'amount' => 'required|numeric',
            'income_date' => 'required',
        ]);

        if (!$validator->validate()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $db = Database::getInstance();
        $tenantId = $this->tenantId();
        $userId = $this->user()['id'];

        // Generate income number
        $incomeNumber = $this->generateIncomeNumber($tenantId);

        $incomeId = $db->insert('income', [
            'tenant_id' => $tenantId,
            'income_category_id' => $_POST['income_category_id'],
            'income_number' => $incomeNumber,
            'income_date' => $_POST['income_date'],
            'customer_name' => $_POST['customer_name'] ?? '',
            'amount' => $_POST['amount'],
            'payment_method' => $_POST['payment_method'] ?? 'cash',
            'description' => $_POST['description'] ?? '',
            'status' => 'received',
            'created_by' => $userId
        ]);

        return $this->json([
            'success' => true,
            'message' => 'Income recorded successfully',
            'id' => $incomeId
        ]);
    }

    /**
     * Show income
     */
    public function show($id)
    {
        $income = $this->getIncome($id);
        if (!$income) {
            return $this->json(['error' => 'Income not found'], 404);
        }

        return $this->view('Income.show', [
            'title' => 'Income #' . $income['income_number'],
            'income' => $income
        ]);
    }

    /**
     * Update income
     */
    public function update($id)
    {
        $income = $this->getIncome($id);
        if (!$income) {
            return $this->json(['error' => 'Income not found'], 404);
        }

        $db = Database::getInstance();
        $db->update('income', $_POST, ['id' => $id, 'tenant_id' => $this->tenantId()]);

        return $this->json(['success' => true, 'message' => 'Income updated successfully']);
    }

    /**
     * Delete income
     */
    public function destroy($id)
    {
        $income = $this->getIncome($id);
        if (!$income) {
            return $this->json(['error' => 'Income not found'], 404);
        }

        $db = Database::getInstance();
        $db->delete('income', ['id' => $id, 'tenant_id' => $this->tenantId()]);

        return $this->json(['success' => true, 'message' => 'Income deleted successfully']);
    }

    /**
     * Get income
     */
    private function getIncome($id)
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM income WHERE id = ? AND tenant_id = ?",
            [$id, $this->tenantId()]
        );
    }

    /**
     * Generate income number
     */
    private function generateIncomeNumber($tenantId)
    {
        $db = Database::getInstance();
        $sequence = $db->fetch(
            "SELECT next_number FROM number_sequences WHERE tenant_id = ? AND sequence_type = 'income'",
            [$tenantId]
        );

        if ($sequence) {
            $nextNumber = $sequence['next_number'];
            $db->update(
                'number_sequences',
                ['next_number' => $nextNumber + 1],
                ['tenant_id' => $tenantId, 'sequence_type' => 'income']
            );
        } else {
            $nextNumber = 3001;
            $db->insert('number_sequences', [
                'tenant_id' => $tenantId,
                'sequence_type' => 'income',
                'prefix' => 'INC',
                'next_number' => $nextNumber + 1
            ]);
        }

        return 'INC-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
