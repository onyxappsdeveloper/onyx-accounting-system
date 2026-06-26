<?php
/**
 * API Routes
 */

$router = $GLOBALS['router'] ?? \App\Core\App::getInstance()->router();

// API endpoints
$router->get('api/customers', 'Customers', 'apiIndex');
$router->post('api/customers', 'Customers', 'apiStore');
$router->get('api/invoices', 'Sales', 'apiInvoices');
$router->post('api/invoices', 'Sales', 'apiStoreInvoice');
