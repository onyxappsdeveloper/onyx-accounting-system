<?php
/**
 * Web Routes
 */

$router = $GLOBALS['router'] ?? \App\Core\App::getInstance()->router();

// Auth Routes
$router->get('auth/login', 'Authentication', 'login');
$router->post('auth/login', 'Authentication', 'handleLogin');
$router->get('auth/register', 'Authentication', 'register');
$router->post('auth/register', 'Authentication', 'handleRegister');
$router->get('auth/logout', 'Authentication', 'logout');
$router->get('auth/forgot-password', 'Authentication', 'forgotPassword');
$router->post('auth/reset-password', 'Authentication', 'resetPassword');

// Dashboard
$router->get('dashboard', 'Dashboard', 'index');

// Customers
$router->get('customers', 'Customers', 'index');
$router->get('customers/create', 'Customers', 'create');
$router->post('customers', 'Customers', 'store');
$router->get('customers/{id}', 'Customers', 'show');
$router->get('customers/{id}/edit', 'Customers', 'edit');
$router->put('customers/{id}', 'Customers', 'update');
$router->delete('customers/{id}', 'Customers', 'destroy');

// Suppliers
$router->get('suppliers', 'Suppliers', 'index');
$router->get('suppliers/create', 'Suppliers', 'create');
$router->post('suppliers', 'Suppliers', 'store');
$router->get('suppliers/{id}', 'Suppliers', 'show');
$router->get('suppliers/{id}/edit', 'Suppliers', 'edit');
$router->put('suppliers/{id}', 'Suppliers', 'update');
$router->delete('suppliers/{id}', 'Suppliers', 'destroy');

// Products
$router->get('products', 'Products', 'index');
$router->get('products/create', 'Products', 'create');
$router->post('products', 'Products', 'store');
$router->get('products/{id}', 'Products', 'show');
$router->get('products/{id}/edit', 'Products', 'edit');
$router->put('products/{id}', 'Products', 'update');
$router->delete('products/{id}', 'Products', 'destroy');

// Invoices
$router->get('invoices', 'Sales', 'invoices');
$router->get('invoices/create', 'Sales', 'createInvoice');
$router->post('invoices', 'Sales', 'storeInvoice');
$router->get('invoices/{id}', 'Sales', 'showInvoice');
$router->get('invoices/{id}/edit', 'Sales', 'editInvoice');
$router->put('invoices/{id}', 'Sales', 'updateInvoice');
$router->delete('invoices/{id}', 'Sales', 'destroyInvoice');
$router->get('invoices/{id}/pdf', 'Sales', 'downloadInvoicePdf');

// Quotations
$router->get('quotations', 'Sales', 'quotations');
$router->get('quotations/create', 'Sales', 'createQuotation');
$router->post('quotations', 'Sales', 'storeQuotation');
$router->get('quotations/{id}', 'Sales', 'showQuotation');
$router->put('quotations/{id}', 'Sales', 'updateQuotation');
$router->delete('quotations/{id}', 'Sales', 'destroyQuotation');

// Cash Sales
$router->get('cash-sales', 'Sales', 'cashSales');
$router->get('cash-sales/create', 'Sales', 'createCashSale');
$router->post('cash-sales', 'Sales', 'storeCashSale');
$router->get('cash-sales/{id}', 'Sales', 'showCashSale');

// Receipts
$router->get('receipts', 'Sales', 'receipts');
$router->get('receipts/{id}', 'Sales', 'showReceipt');
$router->get('receipts/{id}/pdf', 'Sales', 'downloadReceiptPdf');

// Expenses
$router->get('expenses', 'Expenses', 'index');
$router->get('expenses/create', 'Expenses', 'create');
$router->post('expenses', 'Expenses', 'store');
$router->get('expenses/{id}', 'Expenses', 'show');
$router->put('expenses/{id}', 'Expenses', 'update');
$router->delete('expenses/{id}', 'Expenses', 'destroy');

// Income
$router->get('income', 'Income', 'index');
$router->get('income/create', 'Income', 'create');
$router->post('income', 'Income', 'store');
$router->get('income/{id}', 'Income', 'show');
$router->put('income/{id}', 'Income', 'update');
$router->delete('income/{id}', 'Income', 'destroy');

// Accounting
$router->get('accounts', 'Accounting', 'accounts');
$router->get('accounts/create', 'Accounting', 'createAccount');
$router->post('accounts', 'Accounting', 'storeAccount');
$router->get('journal-entries', 'Accounting', 'journalEntries');
$router->get('journal-entries/create', 'Accounting', 'createJournalEntry');
$router->post('journal-entries', 'Accounting', 'storeJournalEntry');
$router->get('general-ledger', 'Accounting', 'generalLedger');
$router->get('trial-balance', 'Accounting', 'trialBalance');
$router->get('balance-sheet', 'Accounting', 'balanceSheet');
$router->get('income-statement', 'Accounting', 'incomeStatement');

// Reports
$router->get('reports', 'Reports', 'index');
$router->get('reports/sales', 'Reports', 'salesReport');
$router->get('reports/expenses', 'Reports', 'expensesReport');
$router->get('reports/inventory', 'Reports', 'inventoryReport');
$router->get('reports/customers', 'Reports', 'customersReport');
$router->get('reports/{id}/pdf', 'Reports', 'downloadPdf');

// Settings
$router->get('settings', 'Settings', 'index');
$router->post('settings', 'Settings', 'update');
$router->get('settings/users', 'Settings', 'users');
$router->post('settings/users', 'Settings', 'createUser');

// Profile
$router->get('profile', 'Authentication', 'profile');
$router->post('profile', 'Authentication', 'updateProfile');
