<?php
/**
 * Auth Routes
 */

$router = $GLOBALS['router'] ?? \App\Core\App::getInstance()->router();

$router->get('auth/login', 'Authentication', 'login');
$router->post('auth/login', 'Authentication', 'handleLogin');
$router->get('auth/register', 'Authentication', 'register');
$router->post('auth/register', 'Authentication', 'handleRegister');
$router->get('auth/logout', 'Authentication', 'logout');
