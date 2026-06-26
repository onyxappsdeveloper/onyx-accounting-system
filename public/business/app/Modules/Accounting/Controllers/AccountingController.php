<?php
/**
 * Accounting Module - Controller
 */

namespace App\Modules\Accounting\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;

class AccountingController extends Controller
{
    /**
     * List accounts
     */
    public function accounts()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();

        $accounts = $db->fetchAll(
            "SELECT * FROM accounts WHERE tenant_id = ? ORDER BY code ASC",
            [$tenantId]
        );

        return $this->view('Accounting.accounts', [
            'title' => 'Chart of Accounts',
            'accounts' => $accounts
        ]);
    }

    /**
     * Show create account form
     */
    public function createAccount()
    {
        $db = Database::getInstance();
        $tenantId = $this->tenantId();

        $accounts = $db->fetchAll(
            "SELECT id, code, name FROM accounts WHERE tenant_id = ? ORDER BY code",
            [$tenantId]
        );

        return $this->view('Accounting.createAccount', [
            'title' => 'Create Account',
            'accounts' => $accounts
        ]);
    }

    /**
     * Store account
     */
    public function storeAccount()
    {
        $validator = new Validator($_POST, [
            'code' => 'required',
            'name' => 'required|min:3',
            'type' => 'required'
        ]);

        if (!$validator->validate()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $db = Database::getInstance();
        $tenantId = $this->tenantId();

        $accountId = $db->insert('accounts', [
            'tenant_id' => $tenantId,
            'code' => $_POST['code'],
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'parent_id' => $_POST['parent_id'] ?? null,
            'is_reconcilable' => $_POST['is_reconcilable'] ?? 0
        ]);

        return $this->json([
            'success' => true,
            'message' => 'Account created successfully',
            'id' => $accountId
        ]);
    }

    /**
     * List journal entries
     */
    public function journalEntries()
    {
        $tenantId = $this->tenantId();
        $db = Database::getInstance();
        $page = $_GET['page'] ?? 1;
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $entries = $db->fetchAll(
            "SELECT * FROM journal_entries WHERE tenant_id = ? ORDER BY entry_date DESC LIMIT ? OFFSET ?",
            [$tenantId, $perPage, $offset]
        );

        $total = $db->fetch(
            "SELECT COUNT(*) as count FROM journal_entries WHERE tenant_id = ?",
            [$tenantId]
        )['count'];

        return $this->view('Accounting.journalEntries', [
            'title' => 'Journal Entries',
            'entries' => $entries,
            'total' => $total,
            'page' => $page
        ]);
    }

    /**
     * Show create journal entry form
     */
    public function createJournalEntry()
    {
        $db = Database::getInstance();
        $tenantId = $this->tenantId();

        $accounts = $db->fetchAll(
            "SELECT id, code, name FROM accounts WHERE tenant_id = ? ORDER BY code",
            [$tenantId]
        );

        return $this->view('Accounting.createJournalEntry', [
            'title' => 'Create Journal Entry',
            'accounts' => $accounts
        ]);
    }

    /**
     * Store journal entry
     */
    public function storeJournalEntry()
    {
        $validator = new Validator($_POST, [
            'entry_date' => 'required',
            'narration' => 'required',
            'lines' => 'required'
        ]);

        if (!$validator->validate()) {
            return $this->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $db = Database::getInstance();
        $tenantId = $this->tenantId();
        $userId = $this->user()['id'];

        try {
            $db->beginTransaction();

            // Create journal entry
            $entryId = $db->insert('journal_entries', [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'entry_date' => $_POST['entry_date'],
                'narration' => $_POST['narration'],
                'source_module' => 'manual'
            ]);

            // Add line items
            $lines = json_decode($_POST['lines'], true);
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($lines as $line) {
                $debit = floatval($line['debit']) ?? 0;
                $credit = floatval($line['credit']) ?? 0;

                $db->insert('journal_entry_lines', [
                    'journal_entry_id' => $entryId,
                    'account_id' => $line['account_id'],
                    'debit' => $debit,
                    'credit' => $credit
                ]);

                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            // Verify debit = credit
            if (abs($totalDebit - $totalCredit) > 0.01) {
                $db->rollback();
                return $this->json(['success' => false, 'message' => 'Debits and credits do not balance'], 422);
            }

            $db->commit();

            return $this->json([
                'success' => true,
                'message' => 'Journal entry created successfully',
                'id' => $entryId
            ]);
        } catch (\Exception $e) {
            $db->rollback();
            error_log('Journal entry failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to create journal entry'], 500);
        }
    }

    /**
     * General ledger
     */
    public function generalLedger()
    {
        // TODO: Implement
        return $this->view('Accounting.generalLedger', [
            'title' => 'General Ledger'
        ]);
    }

    /**
     * Trial balance
     */
    public function trialBalance()
    {
        // TODO: Implement
        return $this->view('Accounting.trialBalance', [
            'title' => 'Trial Balance'
        ]);
    }

    /**
     * Balance sheet
     */
    public function balanceSheet()
    {
        // TODO: Implement
        return $this->view('Accounting.balanceSheet', [
            'title' => 'Balance Sheet'
        ]);
    }

    /**
     * Income statement
     */
    public function incomeStatement()
    {
        // TODO: Implement
        return $this->view('Accounting.incomeStatement', [
            'title' => 'Income Statement'
        ]);
    }
}
