<?php
/**
 * Permissions Configuration
 */

return [
    'super_admin' => ['*'],
    
    'company_admin' => [
        'dashboard.view',
        'customers.view',
        'customers.create',
        'customers.edit',
        'customers.delete',
        'suppliers.view',
        'suppliers.create',
        'suppliers.edit',
        'suppliers.delete',
        'products.view',
        'products.create',
        'products.edit',
        'products.delete',
        'invoices.view',
        'invoices.create',
        'invoices.edit',
        'invoices.delete',
        'payments.view',
        'payments.create',
        'reports.view',
        'settings.manage',
    ],

    'accountant' => [
        'dashboard.view',
        'invoices.view',
        'invoices.create',
        'invoices.edit',
        'payments.view',
        'payments.create',
        'reports.view',
        'accounts.view',
        'accounts.create',
        'accounts.edit',
        'journal_entries.view',
        'journal_entries.create',
    ],

    'cashier' => [
        'dashboard.view',
        'invoices.view',
        'payments.view',
        'payments.create',
        'cash_sales.view',
        'cash_sales.create',
        'receipts.view',
    ],

    'sales_person' => [
        'dashboard.view',
        'customers.view',
        'invoices.view',
        'invoices.create',
        'quotations.view',
        'quotations.create',
    ],

    'store_manager' => [
        'dashboard.view',
        'products.view',
        'inventory.view',
        'inventory.create',
        'stock_transfers.view',
        'stock_transfers.create',
    ],

    'auditor' => [
        'dashboard.view',
        'reports.view',
        'audit_logs.view',
        'invoices.view',
        'payments.view',
    ],

    'read_only' => [
        'dashboard.view',
        'customers.view',
        'invoices.view',
        'reports.view',
    ],
];
